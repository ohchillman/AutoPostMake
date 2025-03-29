<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoPostMake - Сервис парсинга и рерайтинга новостей</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .news-card {
            height: 100%;
            transition: transform 0.3s;
        }
        .news-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .news-image {
            height: 200px;
            object-fit: cover;
        }
        .news-source {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: rgba(0,0,0,0.7);
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.8rem;
        }
        .rewrite-btn {
            cursor: pointer;
        }
        .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }
        .parsing-status {
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .tab-content {
            padding: 20px;
            border: 1px solid #dee2e6;
            border-top: none;
            border-radius: 0 0 5px 5px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="/">AutoPostMake</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="/">Главная</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/index.php">Админ-панель</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if ($activeSourcesCount > 0): ?>
            <div class="parsing-status bg-success text-white">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="mb-0"><i class="bi bi-check-circle-fill"></i> Парсинг новостей активен</h5>
                        <p class="mb-0">Настроено источников: <?php echo $activeSourcesCount; ?></p>
                        <?php if ($lastParsing): ?>
                            <small>Последнее обновление: <?php echo $lastParsing; ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="/admin/parse.php" class="btn btn-light btn-sm">Управление парсингом</a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="parsing-status bg-warning">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="mb-0"><i class="bi bi-exclamation-triangle-fill"></i> Парсинг новостей не настроен</h5>
                        <p class="mb-0">Добавьте источники новостей в админ-панели для начала работы</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="/admin/sources.php" class="btn btn-dark btn-sm">Добавить источники</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <h1 class="mb-4">Последние новости</h1>

        <?php if (empty($news)): ?>
            <div class="alert alert-info">
                <p>Новости пока не загружены. Настройте источники и запустите парсинг в админ-панели.</p>
                <a href="/admin/parse.php" class="btn btn-primary">Перейти к управлению парсингом</a>
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php foreach ($news as $item): ?>
                    <div class="col">
                        <div class="card news-card h-100">
                            <?php if ($item['image_url']): ?>
                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" class="card-img-top news-image" alt="<?php echo htmlspecialchars($item['title']); ?>">
                                <div class="news-source"><?php echo htmlspecialchars($item['source_name']); ?></div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($item['title']); ?></h5>
                                <p class="card-text">
                                    <?php echo substr(strip_tags($item['content']), 0, 150); ?>...
                                </p>
                            </div>
                            <div class="card-footer d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <?php echo date('d.m.Y H:i', strtotime($item['published_at'])); ?>
                                </small>
                                <div>
                                    <?php if ($item['rewrite_count'] > 0): ?>
                                        <span class="badge bg-success rewrite-btn" data-bs-toggle="modal" data-bs-target="#rewriteModal" data-news-id="<?php echo $item['id']; ?>">
                                            <i class="bi bi-magic"></i> <?php echo $item['rewrite_count']; ?> рерайтов
                                        </span>
                                    <?php endif; ?>
                                    <a href="<?php echo htmlspecialchars($item['url']); ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Модальное окно для отображения рерайтов -->
    <div class="modal fade" id="rewriteModal" tabindex="-1" aria-labelledby="rewriteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rewriteModalLabel">Рерайты новости</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="rewriteLoading" class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Загрузка...</span>
                        </div>
                        <p>Загрузка рерайтов...</p>
                    </div>
                    
                    <div id="rewriteContent" style="display: none;">
                        <div class="original-news mb-4">
                            <h4 id="originalTitle"></h4>
                            <p class="text-muted" id="originalSource"></p>
                            <div id="originalContent"></div>
                            <a href="#" id="originalLink" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                                Перейти к оригиналу <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                        </div>
                        
                        <hr>
                        
                        <h5>Рерайты:</h5>
                        <ul class="nav nav-tabs" id="rewriteTabs" role="tablist"></ul>
                        <div class="tab-content" id="rewriteTabContent"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>AutoPostMake</h5>
                    <p>Сервис парсинга и рерайтинга новостей</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>&copy; <?php echo date('Y'); ?> AutoPostMake</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Обработка клика по кнопке рерайтов
        document.addEventListener('DOMContentLoaded', function() {
            const rewriteModal = document.getElementById('rewriteModal');
            if (rewriteModal) {
                rewriteModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const newsId = button.getAttribute('data-news-id');
                    
                    // Показываем индикатор загрузки
                    document.getElementById('rewriteLoading').style.display = 'block';
                    document.getElementById('rewriteContent').style.display = 'none';
                    
                    // Очищаем предыдущие данные
                    document.getElementById('rewriteTabs').innerHTML = '';
                    document.getElementById('rewriteTabContent').innerHTML = '';
                    
                    // Загружаем рерайты
                    fetch('/get_rewrites.php?id=' + newsId)
                        .then(response => response.json())
                        .then(data => {
                            // Скрываем индикатор загрузки
                            document.getElementById('rewriteLoading').style.display = 'none';
                            document.getElementById('rewriteContent').style.display = 'block';
                            
                            // Заполняем данные оригинальной новости
                            document.getElementById('originalTitle').textContent = data.news.title;
                            document.getElementById('originalSource').textContent = 'Источник: ' + data.news.source_name;
                            document.getElementById('originalContent').innerHTML = data.news.content;
                            document.getElementById('originalLink').href = data.news.url;
                            
                            // Создаем вкладки для рерайтов
                            const tabsContainer = document.getElementById('rewriteTabs');
                            const tabContentContainer = document.getElementById('rewriteTabContent');
                            
                            data.rewrites.forEach((rewrite, index) => {
                                // Создаем вкладку
                                const tabId = 'rewrite-' + index;
                                const tabItem = document.createElement('li');
                                tabItem.className = 'nav-item';
                                tabItem.innerHTML = `
                                    <button class="nav-link ${index === 0 ? 'active' : ''}" 
                                            id="${tabId}-tab" 
                                            data-bs-toggle="tab" 
                                            data-bs-target="#${tabId}" 
                                            type="button" 
                                            role="tab" 
                                            aria-controls="${tabId}" 
                                            aria-selected="${index === 0 ? 'true' : 'false'}">
                                        Вариант ${index + 1}
                                    </button>
                                `;
                                tabsContainer.appendChild(tabItem);
                                
                                // Создаем содержимое вкладки
                                const tabContent = document.createElement('div');
                                tabContent.className = `tab-pane fade ${index === 0 ? 'show active' : ''}`;
                                tabContent.id = tabId;
                                tabContent.setAttribute('role', 'tabpanel');
                                tabContent.setAttribute('aria-labelledby', `${tabId}-tab`);
                                
                                tabContent.innerHTML = `
                                    <h5>${rewrite.title}</h5>
                                    <div>${rewrite.content}</div>
                                    <p class="text-muted mt-2">
                                        <small>Создан: ${rewrite.created_at}</small>
                                    </p>
                                `;
                                
                                tabContentContainer.appendChild(tabContent);
                            });
                        })
                        .catch(error => {
                            console.error('Ошибка при загрузке рерайтов:', error);
                            document.getElementById('rewriteLoading').style.display = 'none';
                            document.getElementById('rewriteContent').innerHTML = '<div class="alert alert-danger">Ошибка при загрузке рерайтов</div>';
                            document.getElementById('rewriteContent').style.display = 'block';
                        });
                });
            }
        });
    </script>
</body>
</html>
