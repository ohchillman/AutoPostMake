<!-- Представление для списка источников новостей -->
<div class="mb-3">
    <a href="/admin/sources.php?action=create" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Добавить источник
    </a>
</div>

<?php if (empty($sources)): ?>
    <div class="alert alert-info">
        <p>Источники новостей не добавлены. Добавьте первый источник, чтобы начать парсинг новостей.</p>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Название</th>
                    <th>URL</th>
                    <th>Тип парсера</th>
                    <th>Статус</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sources as $source): ?>
                    <tr>
                        <td><?php echo $source['id']; ?></td>
                        <td><?php echo htmlspecialchars($source['name']); ?></td>
                        <td>
                            <a href="<?php echo htmlspecialchars($source['url']); ?>" target="_blank">
                                <?php echo htmlspecialchars($source['url']); ?>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($source['parser_type']); ?></td>
                        <td>
                            <?php if ($source['active']): ?>
                                <span class="badge bg-success">Активен</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Неактивен</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="/admin/sources.php?action=edit&id=<?php echo $source['id']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="/admin/sources.php?action=toggle&id=<?php echo $source['id']; ?>" class="btn btn-sm btn-outline-warning">
                                    <?php if ($source['active']): ?>
                                        <i class="bi bi-pause-fill"></i>
                                    <?php else: ?>
                                        <i class="bi bi-play-fill"></i>
                                    <?php endif; ?>
                                </a>
                                <a href="/admin/sources.php?action=delete&id=<?php echo $source['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Вы уверены, что хотите удалить этот источник?');">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
