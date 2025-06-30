<div class="card">
    <div class="card-body text-center">
        <h1 class="card-title"><?= htmlspecialchars($content) ?></h1>
        <?php if (isset($user)): ?>
            <div class="alert alert-success mt-4">
                Вы вошли как: <strong><?= htmlspecialchars($user['email']) ?></strong>
            </div>
        <?php endif; ?>
    </div>
</div>