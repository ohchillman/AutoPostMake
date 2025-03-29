<!-- Представление для редактирования источника новостей -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title">Редактирование источника новостей</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="/admin/sources.php?action=update&id=<?php echo $source['id']; ?>">
            <div class="mb-3">
                <label for="name" class="form-label">Название источника</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($source['name']); ?>" required>
                <div class="form-text">Введите понятное название для источника новостей</div>
            </div>
            
            <div class="mb-3">
                <label for="url" class="form-label">URL источника</label>
                <input type="url" class="form-control" id="url" name="url" value="<?php echo htmlspecialchars($source['url']); ?>" required>
                <div class="form-text">Введите полный URL источника новостей (включая http:// или https://)</div>
            </div>
            
            <div class="mb-3">
                <label for="parser_type" class="form-label">Тип парсера</label>
                <select class="form-select" id="parser_type" name="parser_type" required>
                    <option value="">Выберите тип парсера</option>
                    <option value="rss" <?php echo $source['parser_type'] === 'rss' ? 'selected' : ''; ?>>RSS</option>
                    <option value="html" <?php echo $source['parser_type'] === 'html' ? 'selected' : ''; ?>>HTML</option>
                </select>
                <div class="form-text">Выберите тип парсера в зависимости от формата источника</div>
            </div>
            
            <div class="mb-3" id="selector_group" style="display: <?php echo $source['parser_type'] === 'html' ? 'block' : 'none'; ?>;">
                <label for="selector" class="form-label">CSS селектор</label>
                <input type="text" class="form-control" id="selector" name="selector" value="<?php echo htmlspecialchars($source['selector'] ?? ''); ?>">
                <div class="form-text">Введите CSS селектор для извлечения новостей (только для HTML парсера)</div>
            </div>
            
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="active" name="active" <?php echo $source['active'] ? 'checked' : ''; ?>>
                <label class="form-check-label" for="active">Активен</label>
                <div class="form-text">Если отключено, источник не будет использоваться при парсинге</div>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="/admin/sources.php" class="btn btn-secondary">Отмена</a>
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Показывать/скрывать поле селектора в зависимости от выбранного типа парсера
    document.getElementById('parser_type').addEventListener('change', function() {
        const selectorGroup = document.getElementById('selector_group');
        if (this.value === 'html') {
            selectorGroup.style.display = 'block';
        } else {
            selectorGroup.style.display = 'none';
        }
    });
</script>
