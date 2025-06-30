<div class="container mt-4">
    <h1 class="mb-4">Мои опросы</h1>

    <div class="d-grid gap-2 mb-4">
        <a href="/poll/create" class="btn btn-success">Создать новый опрос</a>
    </div>

    <?php if (empty($polls)): ?>
        <div class="alert alert-info">У вас пока нет опросов</div>
    <?php else: ?>
        <div class="list-group">
            <?php foreach ($polls as $poll): ?>
                <div class="list-group-item">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1"><?= htmlspecialchars($poll['title']) ?></h5>
                        <small class="text-muted"><?= $poll['status'] === 'draft' ? 'Черновик' : 'Опубликован' ?></small>
                    </div>
                    <div class="mt-2">
                        <a href="/poll/view/<?= $poll['id'] ?>" class="btn btn-sm btn-outline-primary">Посмотреть</a>
                        <a href="/poll/edit/<?= $poll['id'] ?>" class="btn btn-sm btn-outline-warning">Редактировать</a>
                        <a href="/poll/delete/<?= $poll['id'] ?>" class="btn btn-sm btn-outline-danger"
                            onclick="return confirm('Вы уверены, что хотите удалить этот опрос?')">Удалить</a>
                        <?php if ($poll['status'] === 'draft'): ?>
                            <form method="post" action="/poll/publish/<?= $poll['id'] ?>" class="d-inline">
                                <button type="submit" class="btn btn-sm btn-success">Опубликовать</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>