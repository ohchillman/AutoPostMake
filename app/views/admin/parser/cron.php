<!-- Представление для настройки cron-заданий -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title">Настройка периодического парсинга</h5>
    </div>
    <div class="card-body">
        <p>Настройте интервал автоматического парсинга новостей и рерайтинга.</p>
        
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Рерайтинг будет запускаться через 5 минут после завершения парсинга.
        </div>
        
        <form method="POST" action="/admin/parse.php?action=setup_cron">
            <div class="mb-3">
                <label for="interval" class="form-label">Интервал парсинга (в минутах)</label>
                <select class="form-select" id="interval" name="interval">
                    <option value="15" <?php echo $interval == 15 ? 'selected' : ''; ?>>Каждые 15 минут</option>
                    <option value="30" <?php echo $interval == 30 ? 'selected' : ''; ?>>Каждые 30 минут</option>
                    <option value="60" <?php echo $interval == 60 ? 'selected' : ''; ?>>Каждый час</option>
                    <option value="120" <?php echo $interval == 120 ? 'selected' : ''; ?>>Каждые 2 часа</option>
                    <option value="360" <?php echo $interval == 360 ? 'selected' : ''; ?>>Каждые 6 часов</option>
                    <option value="720" <?php echo $interval == 720 ? 'selected' : ''; ?>>Каждые 12 часов</option>
                    <option value="1440" <?php echo $interval == 1440 ? 'selected' : ''; ?>>Раз в день</option>
                </select>
                <div class="form-text">Выберите, как часто будет выполняться парсинг новостей.</div>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="/admin/parse.php" class="btn btn-secondary">Отмена</a>
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </form>
    </div>
</div>
