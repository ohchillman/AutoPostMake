<!-- Представление для главной страницы админ-панели -->
<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">Статистика</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card text-white bg-primary mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Источники новостей</h5>
                                <p class="card-text display-4"><?php echo $sourcesCount ?? 0; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card text-white bg-success mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Новости</h5>
                                <p class="card-text display-4"><?php echo $newsCount ?? 0; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="card text-white bg-info mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Рерайты</h5>
                                <p class="card-text display-4"><?php echo $rewritesCount ?? 0; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card <?php echo isset($makeComToken) && $makeComToken ? 'text-white bg-success' : 'text-white bg-danger'; ?> mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Make.com API</h5>
                                <p class="card-text"><?php echo isset($makeComToken) && $makeComToken ? 'Настроен' : 'Не настроен'; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">Последние действия</h5>
            </div>
            <div class="card-body">
                <?php if (empty($logs)): ?>
                    <p class="text-muted">Нет данных о последних действиях</p>
                <?php else: ?>
                    <ul class="list-group">
                        <?php foreach ($logs as $log): ?>
                            <li class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?php echo $log['message']; ?></h6>
                                    <small><?php echo $log['created_at']; ?></small>
                                </div>
                                <small class="text-muted">Источник: <?php echo $log['source_name']; ?></small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Быстрые действия</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="/admin/sources.php?action=create" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Добавить источник новостей
                    </a>
                    <a href="/admin/tokens.php" class="btn btn-success">
                        <i class="bi bi-key"></i> Настроить API токены
                    </a>
                    <a href="/admin/parse.php" class="btn btn-warning">
                        <i class="bi bi-arrow-repeat"></i> Запустить парсинг вручную
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
