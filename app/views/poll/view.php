<div class="container mt-4">
    <?php if (isset($_SESSION['user']) && $_SESSION['user']['id'] == $poll['user_id']): ?>
        <div class="mb-3">
            <a href="/poll/edit/<?= $poll['id'] ?>" class="btn btn-warning">Редактировать опрос</a>
            <a href="/poll/delete/<?= $poll['id'] ?>" class="btn btn-danger" 
               onclick="return confirm('Вы уверены, что хотите удалить этот опрос?')">Удалить опрос</a>
        </div>
    <?php endif; ?>
    <h1 class="mb-4"><?= htmlspecialchars($poll['title']) ?></h1>

    <form method="post" action="/poll/vote">
        <input type="hidden" name="poll_id" value="<?= $poll['id'] ?>">

        <?php foreach ($poll['questions'] as $question): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5><?= htmlspecialchars($question['question_text']) ?></h5>
                </div>
                <div class="card-body">
                    <?php foreach ($question['answers'] as $answer): ?>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="answers[<?= $question['id'] ?>]"
                                id="answer_<?= $answer['id'] ?>" value="<?= $answer['id'] ?>" required>
                            <label class="form-check-label" for="answer_<?= $answer['id'] ?>">
                                <?= htmlspecialchars($answer['answer_text']) ?>
                                <span class="badge bg-secondary ms-2"><?= $answer['votes'] ?> голосов</span>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary btn-lg">Голосовать</button>
        </div>
    </form>
</div>