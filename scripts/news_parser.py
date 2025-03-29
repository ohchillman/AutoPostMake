#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import sys
import os
import json
import mysql.connector
import feedparser
import requests
from bs4 import BeautifulSoup
from datetime import datetime
import logging
from urllib.parse import urlparse

# Настройка логирования
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler("logs/parser.log"),
        logging.StreamHandler()
    ]
)
logger = logging.getLogger("news_parser")

# Класс для работы с базой данных
class Database:
    def __init__(self, host, user, password, database):
        self.connection = mysql.connector.connect(
            host=host,
            user=user,
            password=password,
            database=database
        )
        self.cursor = self.connection.cursor(dictionary=True)
    
    def get_active_sources(self):
        query = "SELECT * FROM news_sources WHERE active = 1"
        self.cursor.execute(query)
        return self.cursor.fetchall()
    
    def get_source_by_id(self, source_id):
        query = "SELECT * FROM news_sources WHERE id = %s"
        self.cursor.execute(query, (source_id,))
        return self.cursor.fetchone()
    
    def save_news(self, source_id, title, content, url, image_url=None, published_at=None):
        # Проверяем, существует ли уже новость с таким URL
        check_query = "SELECT id FROM news WHERE url = %s"
        self.cursor.execute(check_query, (url,))
        existing = self.cursor.fetchone()
        
        if existing:
            logger.info(f"Новость с URL {url} уже существует в базе данных")
            return existing['id']
        
        # Если новости нет, добавляем её
        query = """
        INSERT INTO news (source_id, title, content, url, image_url, published_at, parsed_at)
        VALUES (%s, %s, %s, %s, %s, %s, NOW())
        """
        
        if published_at is None:
            published_at = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
        
        self.cursor.execute(query, (source_id, title, content, url, image_url, published_at))
        self.connection.commit()
        
        return self.cursor.lastrowid
    
    def log_parsing(self, source_id, status, message=None):
        query = """
        INSERT INTO parsing_logs (source_id, status, message, created_at)
        VALUES (%s, %s, %s, NOW())
        """
        self.cursor.execute(query, (source_id, status, message))
        self.connection.commit()
    
    def close(self):
        self.cursor.close()
        self.connection.close()

# Базовый класс парсера
class BaseParser:
    def __init__(self, db, source):
        self.db = db
        self.source = source
    
    def parse(self):
        raise NotImplementedError("Subclasses must implement parse() method")
    
    def clean_text(self, text):
        if text:
            return text.strip()
        return ""
    
    def log_success(self, message=None):
        if not message:
            message = f"Успешно выполнен парсинг источника {self.source['name']}"
        self.db.log_parsing(self.source['id'], "success", message)
    
    def log_error(self, error):
        message = f"Ошибка при парсинге источника {self.source['name']}: {str(error)}"
        logger.error(message)
        self.db.log_parsing(self.source['id'], "error", message)

# Парсер для RSS-лент
class RssParser(BaseParser):
    def parse(self):
        try:
            logger.info(f"Начинаем парсинг RSS-ленты: {self.source['url']}")
            
            feed = feedparser.parse(self.source['url'])
            
            if not feed.entries:
                self.log_error("RSS-лента не содержит записей")
                return []
            
            news_items = []
            
            for entry in feed.entries[:10]:  # Берем только первые 10 новостей
                title = self.clean_text(entry.get('title', ''))
                link = entry.get('link', '')
                
                # Получаем содержимое новости
                content = ""
                if 'content' in entry:
                    content = entry.content[0].value
                elif 'summary' in entry:
                    content = entry.summary
                elif 'description' in entry:
                    content = entry.description
                
                content = self.clean_text(content)
                
                # Получаем дату публикации
                published = None
                if 'published_parsed' in entry and entry.published_parsed:
                    published = datetime(*entry.published_parsed[:6]).strftime('%Y-%m-%d %H:%M:%S')
                
                # Ищем изображение
                image_url = None
                if 'media_content' in entry and entry.media_content:
                    for media in entry.media_content:
                        if 'url' in media and media.url:
                            image_url = media.url
                            break
                
                if title and link and content:
                    # Сохраняем новость в базу данных
                    news_id = self.db.save_news(
                        self.source['id'],
                        title,
                        content,
                        link,
                        image_url,
                        published
                    )
                    
                    if news_id:
                        news_items.append({
                            'id': news_id,
                            'title': title,
                            'url': link
                        })
            
            self.log_success(f"Добавлено {len(news_items)} новостей из RSS-ленты {self.source['name']}")
            return news_items
            
        except Exception as e:
            self.log_error(e)
            return []

# Парсер для HTML-страниц
class HtmlParser(BaseParser):
    def parse(self):
        try:
            logger.info(f"Начинаем парсинг HTML-страницы: {self.source['url']}")
            
            # Получаем HTML-страницу
            headers = {
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            }
            response = requests.get(self.source['url'], headers=headers, timeout=10)
            response.raise_for_status()
            
            # Парсим HTML
            soup = BeautifulSoup(response.text, 'html.parser')
            
            # Используем селектор из настроек источника
            selector = self.source['selector']
            if not selector:
                self.log_error("Не указан CSS-селектор для HTML-парсера")
                return []
            
            # Находим элементы по селектору
            elements = soup.select(selector)
            
            if not elements:
                self.log_error(f"Не найдены элементы по селектору: {selector}")
                return []
            
            news_items = []
            base_url = self.get_base_url(self.source['url'])
            
            for element in elements[:10]:  # Берем только первые 10 новостей
                # Ищем заголовок
                title_element = element.select_one('h1, h2, h3, h4, .title, [class*="title"]')
                if not title_element:
                    continue
                
                title = self.clean_text(title_element.get_text())
                
                # Ищем ссылку
                link_element = element.select_one('a') or title_element.find_parent('a')
                if not link_element or not link_element.has_attr('href'):
                    continue
                
                link = link_element['href']
                
                # Преобразуем относительные ссылки в абсолютные
                if not link.startswith(('http://', 'https://')):
                    link = base_url + link if not link.startswith('/') else base_url + '/' + link
                
                # Ищем контент
                content_element = element.select_one('p, .content, .description, [class*="content"], [class*="description"]')
                content = ""
                if content_element:
                    content = self.clean_text(content_element.get_text())
                
                # Если контента нет или он слишком короткий, пытаемся получить его со страницы новости
                if not content or len(content) < 100:
                    try:
                        article_response = requests.get(link, headers=headers, timeout=10)
                        article_soup = BeautifulSoup(article_response.text, 'html.parser')
                        
                        # Ищем основной контент статьи
                        article_content = article_soup.select_one('article, .article, .post, .content, [class*="article"], [class*="post"], [class*="content"]')
                        if article_content:
                            content = self.clean_text(article_content.get_text())
                    except Exception as e:
                        logger.warning(f"Не удалось получить контент статьи {link}: {str(e)}")
                
                # Ищем изображение
                image_url = None
                img_element = element.select_one('img')
                if img_element and img_element.has_attr('src'):
                    image_url = img_element['src']
                    
                    # Преобразуем относительные ссылки в абсолютные
                    if not image_url.startswith(('http://', 'https://')):
                        image_url = base_url + image_url if not image_url.startswith('/') else base_url + '/' + image_url
                
                if title and link:
                    # Сохраняем новость в базу данных
                    news_id = self.db.save_news(
                        self.source['id'],
                        title,
                        content,
                        link,
                        image_url
                    )
                    
                    if news_id:
                        news_items.append({
                            'id': news_id,
                            'title': title,
                            'url': link
                        })
            
            self.log_success(f"Добавлено {len(news_items)} новостей из HTML-страницы {self.source['name']}")
            return news_items
            
        except Exception as e:
            self.log_error(e)
            return []
    
    def get_base_url(self, url):
        parsed_url = urlparse(url)
        return f"{parsed_url.scheme}://{parsed_url.netloc}"

# Основная функция парсинга
def parse_news(source_id=None):
    try:
        # Подключаемся к базе данных
        db = Database(
            host="localhost",
            user="root",
            password="",
            database="autopostmake"
        )
        
        # Получаем активные источники
        if source_id:
            sources = [db.get_source_by_id(source_id)]
            if not sources[0]:
                logger.error(f"Источник с ID {source_id} не найден")
                return
        else:
            sources = db.get_active_sources()
        
        if not sources:
            logger.warning("Нет активных источников для парсинга")
            return
        
        # Парсим каждый источник
        all_news = []
        for source in sources:
            logger.info(f"Обрабатываем источник: {source['name']} (ID: {source['id']})")
            
            # Выбираем подходящий парсер
            parser = None
            if source['parser_type'] == 'rss':
                parser = RssParser(db, source)
            elif source['parser_type'] == 'html':
                parser = HtmlParser(db, source)
            else:
                logger.error(f"Неизвестный тип парсера: {source['parser_type']}")
                continue
            
            # Запускаем парсинг
            news_items = parser.parse()
            all_news.extend(news_items)
        
        logger.info(f"Парсинг завершен. Всего добавлено {len(all_news)} новостей")
        
        # Закрываем соединение с базой данных
        db.close()
        
        return all_news
        
    except Exception as e:
        logger.error(f"Ошибка при выполнении парсинга: {str(e)}")
        return []

# Запуск скрипта
if __name__ == "__main__":
    # Проверяем аргументы командной строки
    source_id = None
    if len(sys.argv) > 1:
        try:
            source_id = int(sys.argv[1])
        except ValueError:
            logger.error("Неверный формат ID источника")
            sys.exit(1)
    
    # Запускаем парсинг
    parse_news(source_id)
