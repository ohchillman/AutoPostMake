-- База данных для сервиса парсинга и рерайтинга новостей

-- Таблица для хранения источников новостей
CREATE TABLE IF NOT EXISTS news_sources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    url VARCHAR(512) NOT NULL,
    parser_type VARCHAR(50) NOT NULL, -- тип парсера (rss, html, etc)
    selector VARCHAR(255), -- CSS селектор для HTML парсера
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Таблица для хранения API токенов
CREATE TABLE IF NOT EXISTS api_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service VARCHAR(100) NOT NULL, -- название сервиса (make.com)
    token VARCHAR(512) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Таблица для хранения новостей
CREATE TABLE IF NOT EXISTS news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    source_id INT NOT NULL,
    title VARCHAR(512) NOT NULL,
    content TEXT NOT NULL,
    url VARCHAR(512) NOT NULL,
    image_url VARCHAR(512),
    published_at TIMESTAMP,
    parsed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (source_id) REFERENCES news_sources(id) ON DELETE CASCADE
);

-- Таблица для хранения рерайтов новостей
CREATE TABLE IF NOT EXISTS news_rewrites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    news_id INT NOT NULL,
    title VARCHAR(512) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (news_id) REFERENCES news(id) ON DELETE CASCADE
);

-- Таблица для хранения пользователей админ-панели
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Таблица для хранения логов парсинга
CREATE TABLE IF NOT EXISTS parsing_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    source_id INT NOT NULL,
    status VARCHAR(50) NOT NULL, -- success, error
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (source_id) REFERENCES news_sources(id) ON DELETE CASCADE
);
