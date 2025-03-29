<!-- Представление для настройки API токенов -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title">Настройка API токенов</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="/admin/tokens.php?action=save_make_com">
            <div class="mb-3">
                <label for="token" class="form-label">Токен Make.com</label>
                <input type="text" class="form-control" id="token" name="token" 
                    value="<?php echo isset($makeComToken) ? htmlspecialchars($makeComToken['token']) : ''; ?>" required>
                <div class="form-text">
                    Введите API токен для доступа к сервису Make.com. Этот токен будет использоваться для рерайтинга новостей.
                </div>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </form>
        
        <hr>
        
        <div class="mt-4">
            <h6>Как получить токен Make.com:</h6>
            <ol>
                <li>Зарегистрируйтесь или войдите в аккаунт на сайте <a href="https://www.make.com" target="_blank">Make.com</a></li>
                <li>Перейдите в настройки профиля</li>
                <li>Найдите раздел API токенов</li>
                <li>Создайте новый токен с необходимыми правами доступа</li>
                <li>Скопируйте токен и вставьте его в поле выше</li>
            </ol>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i> Храните токен в безопасности. Не передавайте его третьим лицам.
            </div>
        </div>
    </div>
</div>
