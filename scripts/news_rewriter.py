#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import sys
import os
import json
import requests
import mysql.connector
from datetime import datetime
import logging

# Настройка логирования
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler("logs/rewriter.log"),
        logging.StreamHandler()
    ]
)
logger = logging.getLogger("news_rewriter")

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
    
    def get_make_com_token(self):
        query = "SELECT token FROM api_tokens WHERE service = 'make.com'"
        self.cursor.execute(query)
        result = self.cursor.fetchone()
        return result['token'] if result else None
    
    def get_news_without_rewrites(self, limit=5):
        query = """
        SELECT n.* FROM news n
        LEFT JOIN (
            SELECT news_id, COUNT(*) as rewrite_count
            FROM news_rewrites
            GROUP BY news_id
        ) r ON n.id = r.news_id
        WHERE r.rewrite_count IS NULL OR r.rewrite_count < 10
        ORDER BY n.parsed_at DESC
        LIMIT %s
        """
        self.cursor.execute(query, (limit,))
        return self.cursor.fetchall()
    
    def save_rewrite(self, news_id, title, content):
        query = """
        INSERT INTO news_rewrites (news_id, title, content, created_at)
        VALUES (%s, %s, %s, NOW())
        """
        self.cursor.execute(query, (news_id, title, content))
        self.connection.commit()
        return self.cursor.lastrowid
    
    def get_rewrite_count(self, news_id):
        query = "SELECT COUNT(*) as count FROM news_rewrites WHERE news_id = %s"
        self.cursor.execute(query, (news_id,))
        result = self.cursor.fetchone()
        return result['count'] if result else 0
    
    def close(self):
        self.cursor.close()
        self.connection.close()

# Класс для работы с Make.com API
class MakeComRewriter:
    def __init__(self, token):
        self.token = token
        self.api_url = "https://hook.eu1.make.com/your_webhook_endpoint_here"  # Замените на ваш вебхук
    
    def rewrite_text(self, title, content):
        try:
            # Подготавливаем данные для отправки
            payload = {
                "token": self.token,
                "title": title,
                "content": content
            }
            
            # Отправляем запрос к Make.com
            response = requests.post(
                self.api_url,
                json=payload,
                headers={"Content-Type": "application/json"},
                timeout=30
            )
            
            # Проверяем ответ
            if response.status_code == 200:
                result = response.json()
                if "rewritten_title" in result and "rewritten_content" in result:
                    return {
                        "title": result["rewritten_title"],
                        "content": result["rewritten_content"]
                    }
                else:
                    logger.error(f"Неверный формат ответа от Make.com: {result}")
            else:
                logger.error(f"Ошибка при обращении к Make.com: {response.status_code} - {response.text}")
            
            return None
            
        except Exception as e:
            logger.error(f"Ошибка при рерайтинге текста: {str(e)}")
            return None

# Основная функция рерайтинга
def rewrite_news():
    try:
        # Подключаемся к базе данных
        db = Database(
            host="localhost",
            user="root",
            password="",
            database="autopostmake"
        )
        
        # Получаем токен Make.com
        token = db.get_make_com_token()
        if not token:
            logger.error("Токен Make.com не найден в базе данных")
            return
        
        # Создаем экземпляр рерайтера
        rewriter = MakeComRewriter(token)
        
        # Получаем новости без рерайтов
        news_items = db.get_news_without_rewrites()
        if not news_items:
            logger.info("Нет новостей для рерайтинга")
            return
        
        # Обрабатываем каждую новость
        for news in news_items:
            logger.info(f"Обрабатываем новость ID {news['id']}: {news['title']}")
            
            # Получаем текущее количество рерайтов для этой новости
            rewrite_count = db.get_rewrite_count(news['id'])
            
            # Определяем, сколько рерайтов нужно создать
            rewrites_to_create = 10 - rewrite_count
            
            # Создаем рерайты
            for i in range(rewrites_to_create):
                logger.info(f"Создаем рерайт {i+1}/{rewrites_to_create} для новости ID {news['id']}")
                
                # Отправляем запрос на рерайтинг
                rewrite = rewriter.rewrite_text(news['title'], news['content'])
                
                if rewrite:
                    # Сохраняем рерайт в базу данных
                    rewrite_id = db.save_rewrite(
                        news['id'],
                        rewrite['title'],
                        rewrite['content']
                    )
                    
                    logger.info(f"Рерайт сохранен с ID {rewrite_id}")
                else:
                    logger.error(f"Не удалось создать рерайт для новости ID {news['id']}")
                    break
        
        # Закрываем соединение с базой данных
        db.close()
        
        logger.info("Рерайтинг завершен")
        
    except Exception as e:
        logger.error(f"Ошибка при выполнении рерайтинга: {str(e)}")

# Запуск скрипта
if __name__ == "__main__":
    rewrite_news()
