<!-- Представление для страницы управления парсингом -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Ручной запуск парсинга</h5>
            </div>
            <div class="card-body">
                <p>Запустите парсинг новостей вручную для всех активных источников или выберите конкретный источник.</p>
                
                <div class="d-grid gap-2">
                    <a href="/admin/parse.php?action=run" class="btn btn-primary">
                        <i class="bi bi-arrow-repeat"></i> Запустить парсинг для всех источников
                    </a>
                </div>
                
                <?php if (!empty($sources)): ?>
                    <hr>
                    <h6>Запуск для конкретного источника:</h6>
                    <div class="list-group">
                        <?php foreach ($sources as $source): ?>
                            <a href="/admin/parse.php?action=run&id=<?php echo $source['id']; ?>" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($source['name']); ?></h6>
                                    <small><?php echo $source['parser_type']; ?></small>
                                </div>
                                <small class="text-muted"><?php echo htmlspecialchars($source['url']); ?></small>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Рерайтинг новостей</h5>
            </div>
            <div class="card-body">
                <p>Запустите процесс рерайтинга новостей с помощью Make.com API.</p>
                
                <?php if (isset($makeComToken) && $makeComToken): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> Токен Make.com настроен
                    </div>
                    
                    <div class="d-grid gap-2">
                        <a href="/admin/parse.php?action=rewrite" class="btn btn-success">
                            <i class="bi bi-magic"></i> Запустить рерайтинг новостей
                        </a>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> Токен Make.com не настроен
                    </div>
                    
                    <div class="d-grid gap-2">
                        <a href="/admin/tokens.php" class="btn btn-warning">
                            <i class="bi bi-key"></i> Настроить токен Make.com
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Периодический парсинг</h5>
        <a href="/admin/parse.php?action=setup_cron" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-clock"></i> Настроить расписание
        </a>
    </div>
    <div class="card-body">
        <p>Настройте периодический запуск парсинга новостей с помощью cron.</p>
        
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Периодический парсинг позволяет автоматически получать новости из настроенных источников через заданные интервалы времени.
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title">Журнал парсинга</h5>
    </div>
    <div class="card-body">
        <?php if (empty($logs)): ?>
            <p class="text-muted">Журнал парсинга пуст</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Дата</th>
                            <th>Источник</th>
                            <th>Статус</th>
                            <th>Сообщение</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?php echo $log['created_at']; ?></td>
                                <td><?php echo htmlspecialchars($log['source_name']); ?></td>
                                <td>
                                    <?php if ($log['status'] === 'success'): ?>
                                        <span class="badge bg-success">Успех</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Ошибка</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($log['message']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
