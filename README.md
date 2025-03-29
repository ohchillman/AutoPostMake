# AutoPostMake

Сервис парсинга и рерайтинга новостей с использованием make.com API.

## Описание

AutoPostMake - это веб-приложение, которое позволяет:
- Парсить новости из различных источников (RSS-ленты, HTML-страницы)
- Создавать рерайты новостей с помощью API make.com
- Управлять источниками новостей через админ-панель
- Настраивать токены для интеграции с make.com
- Отображать новости и их рерайты на главной странице

## Пошаговая инструкция по установке на чистом Ubuntu

### 1. Обновление системы

```bash
sudo apt update
sudo apt upgrade -y
```

### 2. Установка необходимых пакетов

```bash
# Установка веб-сервера, PHP и MySQL
sudo apt install -y apache2 mysql-server php php-mysql php-curl php-mbstring php-xml php-zip

# Установка Python и необходимых пакетов
sudo apt install -y python3 python3-full python3-pip python3-venv

# Вариант 1: Установка пакетов через apt (рекомендуется)
sudo apt install -y python3-requests python3-bs4 python3-mysql.connector python3-feedparser

# Вариант 2: Создание виртуального окружения (если пакеты недоступны через apt)
python3 -m venv /var/www/autopostmake/venv
source /var/www/autopostmake/venv/bin/activate
pip install requests beautifulsoup4 mysql-connector-python feedparser

# Примечание: при использовании виртуального окружения необходимо 
# обновить пути к Python в скриптах запуска или использовать полный путь
# к интерпретатору Python из виртуального окружения
```

### 3. Настройка MySQL

```bash
# Запуск MySQL
sudo systemctl start mysql
sudo systemctl enable mysql

# Настройка безопасности MySQL (следуйте инструкциям)
sudo mysql_secure_installation

# Создание базы данных и пользователя
sudo mysql -e "CREATE DATABASE autopostmake CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql -e "CREATE USER 'autopostmake'@'localhost' IDENTIFIED BY 'your_password';"
sudo mysql -e "GRANT ALL PRIVILEGES ON autopostmake.* TO 'autopostmake'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"
```

### 4. Настройка Apache

```bash
# Создание виртуального хоста
sudo nano /etc/apache2/sites-available/autopostmake.conf
```

Добавьте следующее содержимое:

```apache
<VirtualHost *:80>
    ServerName autopostmake.local
    DocumentRoot /var/www/autopostmake/public
    
    <Directory /var/www/autopostmake/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/autopostmake_error.log
    CustomLog ${APACHE_LOG_DIR}/autopostmake_access.log combined
</VirtualHost>
```

Активируйте виртуальный хост и модуль rewrite:

```bash
sudo a2ensite autopostmake.conf
sudo a2enmod rewrite
sudo systemctl restart apache2
```

**Важно**: Убедитесь, что DocumentRoot указывает на директорию `public` внутри вашего проекта. Если вы видите ошибку "Not Found", проверьте следующее:

1. Правильность пути в конфигурации Apache (должен быть `/var/www/autopostmake/public`)
2. Наличие файлов в директории public (особенно index.php)
3. Права доступа к файлам (www-data должен иметь доступ на чтение)
4. Активацию модуля rewrite (`sudo a2enmod rewrite`)
5. Перезапуск Apache после внесения изменений (`sudo systemctl restart apache2`)

Для отладки проблем с Apache проверьте журналы ошибок:
```bash
sudo tail -f /var/log/apache2/error.log
```

### 5. Клонирование репозитория

```bash
# Установка Git
sudo apt install -y git

# Клонирование репозитория
cd /var/www
sudo git clone https://github.com/ohchillman/AutoPostMake.git autopostmake
sudo chown -R www-data:www-data /var/www/autopostmake
```

### 6. Настройка конфигурации

```bash
# Редактирование файла конфигурации
sudo nano /var/www/autopostmake/config/config.php
```

Измените параметры подключения к базе данных:

```php
// Конфигурация базы данных
define('DB_HOST', 'localhost');
define('DB_NAME', 'autopostmake');
define('DB_USER', 'autopostmake');
define('DB_PASS', 'your_password');
```

### 7. Инициализация базы данных

```bash
# Запуск скрипта инициализации базы данных
cd /var/www/autopostmake
sudo php scripts/init_db.php
```

### 8. Настройка прав доступа и директории для логов

```bash
# Установка правильных прав доступа
sudo chown -R www-data:www-data /var/www/autopostmake
sudo chmod -R 755 /var/www/autopostmake

# Создание директории для логов с правами на запись
sudo mkdir -p /var/www/autopostmake/logs
sudo chown -R www-data:www-data /var/www/autopostmake/logs
sudo chmod 777 /var/www/autopostmake/logs
```

**Важно**: Скрипты парсера и рерайтера сохраняют логи в директорию `logs/`. Если эта директория не существует или у пользователя нет прав на запись в неё, скрипты будут выдавать ошибку `Permission denied`.

### 9. Настройка cron для периодического парсинга

```bash
# Открытие редактора cron
sudo crontab -e
```

Добавьте следующие строки:

```
# Запуск парсера каждый час
0 * * * * php /var/www/autopostmake/scripts/run_parser.php > /dev/null 2>&1

# Запуск рерайтера через 5 минут после парсера
5 * * * * php /var/www/autopostmake/scripts/run_rewriter.php > /dev/null 2>&1
```

### 10. Настройка локального хоста (опционально)

Если вы хотите использовать имя домена `autopostmake.local`, добавьте его в файл hosts:

```bash
sudo nano /etc/hosts
```

Добавьте строку:

```
127.0.0.1 autopostmake.local
```

## Использование

1. Откройте браузер и перейдите по адресу `http://autopostmake.local` или `http://localhost`
2. Для доступа к админ-панели перейдите по адресу `http://autopostmake.local/admin` или `http://localhost/admin`
3. Используйте логин `admin` и пароль `admin` для первого входа (не забудьте изменить пароль)

## Функциональность админ-панели

1. **Управление источниками новостей**
   - Добавление новых источников (RSS или HTML)
   - Редактирование существующих источников
   - Удаление источников

2. **Настройка токенов make.com**
   - Добавление и обновление токена для API make.com

3. **Управление парсингом**
   - Ручной запуск парсинга для всех источников или выбранного источника
   - Настройка периодического парсинга
   - Просмотр журнала парсинга

4. **Управление рерайтингом**
   - Ручной запуск рерайтинга новостей
   - Просмотр статистики рерайтинга

## Структура проекта

```
AutoPostMake/
├── app/
│   ├── controllers/    # Контроллеры приложения
│   ├── models/         # Модели для работы с данными
│   └── views/          # Представления (шаблоны)
├── config/             # Конфигурационные файлы
├── database/           # SQL-скрипты для базы данных
├── public/             # Публичные файлы (точка входа)
└── scripts/            # Python-скрипты для парсинга и рерайтинга
```

## Требования к системе

- Ubuntu 20.04 или новее
- PHP 7.4 или новее
- MySQL 8.0 или новее
- Python 3.8 или новее
- Apache 2.4 или новее

## Примечания по безопасности

- После установки измените пароль администратора
- Используйте HTTPS для продакшн-окружения
- Регулярно обновляйте систему и зависимости
- Ограничьте доступ к админ-панели по IP при необходимости

## Устранение неполадок

### Проблемы с подключением к базе данных

Проверьте настройки подключения в файле `config/config.php` и убедитесь, что MySQL-сервер запущен:

```bash
sudo systemctl status mysql
```

### Проблемы с правами доступа

Если возникают ошибки доступа к файлам, проверьте права:

```bash
sudo chown -R www-data:www-data /var/www/autopostmake
sudo chmod -R 755 /var/www/autopostmake
```

### Проблемы с парсингом

Проверьте журнал парсинга в админ-панели или запустите скрипт вручную для отладки:

```bash
cd /var/www/autopostmake
python3 scripts/news_parser.py
```

## Лицензия

Этот проект распространяется под лицензией MIT.
