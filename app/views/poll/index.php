<div class="container mt-4">
    <h1 class="mb-4">Доступные опросы</h1>

    <?php if (empty($polls)): ?>
        <div class="alert alert-info">Нет доступных опросов</div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($polls as $poll): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($poll['title']) ?></h5>
                            <p class="card-text">
                                <small class="text-muted">
                                    Автор: <?= htmlspecialchars($poll['first_name'] . ' ' . $poll['last_name']) ?>
                                </small>
                            </p>
                            <a href="/poll/view/<?= $poll['id'] ?>" class="btn btn-primary">Пройти опрос</a>
                            <?php if (isset($_SESSION['user']) && $_SESSION['user']['id'] == $poll['user_id']): ?>
                                <div class="mt-2">
                                    <a href="/poll/edit/<?= $poll['id'] ?>" class="btn btn-sm btn-outline-warning">Редактировать</a>
                                    <a href="/poll/delete/<?= $poll['id'] ?>" class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Вы уверены, что хотите удалить этот опрос?')">Удалить</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>