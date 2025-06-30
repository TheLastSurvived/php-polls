<div class="container mt-4">
    <h1 class="mb-4">Редактирование опроса</h1>
    
    <form method="post">
        <div class="mb-3">
            <label for="title" class="form-label">Название опроса</label>
            <input type="text" class="form-control" id="title" name="title" 
                   value="<?= htmlspecialchars($poll['title']) ?>" required>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Статус</label>
            <select class="form-select" name="status">
                <option value="draft" <?= $poll['status'] === 'draft' ? 'selected' : '' ?>>Черновик</option>
                <option value="published" <?= $poll['status'] === 'published' ? 'selected' : '' ?>>Опубликован</option>
            </select>
        </div>
        
        <div id="questions-container">
            <?php foreach ($poll['questions'] as $question): ?>
                <div class="card mb-3 question-item">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Вопрос</h5>
                        <button type="button" class="btn btn-sm btn-danger remove-question"
                                data-id="<?= $question['id'] ?>">Удалить вопрос</button>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Текст вопроса</label>
                            <input type="text" class="form-control" name="questions[<?= $question['id'] ?>]" 
                                   value="<?= htmlspecialchars($question['question_text']) ?>" required>
                        </div>
                        
                        <div class="answers-container mb-3">
                            <?php foreach ($question['answers'] as $answer): ?>
                                <div class="input-group mb-2 answer-item">
                                    <input type="text" class="form-control" 
                                           name="answers[<?= $answer['id'] ?>]"
                                           value="<?= htmlspecialchars($answer['answer_text']) ?>" required>
                                    <button class="btn btn-outline-danger remove-answer" type="button"
                                            data-id="<?= $answer['id'] ?>">×</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <button type="button" class="btn btn-sm btn-outline-secondary add-answer"
                                data-question="<?= $question['id'] ?>">Добавить вариант ответа</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="mb-3">
            <button type="button" class="btn btn-secondary" id="add-question">Добавить вопрос</button>
        </div>
        
        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary btn-lg">Сохранить изменения</button>
            <a href="/poll/delete/<?= $poll['id'] ?>" class="btn btn-danger btn-lg" 
               onclick="return confirm('Вы уверены, что хотите удалить этот опрос?')">Удалить опрос</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Добавление нового вопроса
    document.getElementById('add-question').addEventListener('click', function() {
        const container = document.getElementById('questions-container');
        const newQuestionId = Date.now(); // Временный ID
        
        const questionDiv = document.createElement('div');
        questionDiv.className = 'card mb-3 question-item';
        questionDiv.innerHTML = `
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Новый вопрос</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Текст вопроса</label>
                    <input type="text" class="form-control" 
                           name="new_questions[${newQuestionId}][text]" required>
                </div>
                <div class="answers-container mb-3"></div>
                <button type="button" class="btn btn-sm btn-outline-secondary add-new-answer"
                        data-question="${newQuestionId}">Добавить вариант ответа</button>
            </div>
        `;
        
        container.appendChild(questionDiv);
        
        // Добавляем 2 ответа по умолчанию
        const answersContainer = questionDiv.querySelector('.answers-container');
        addNewAnswer(answersContainer, newQuestionId);
        addNewAnswer(answersContainer, newQuestionId);
    });

    // Добавление ответа к существующему вопросу
    document.querySelectorAll('.add-answer').forEach(btn => {
        btn.addEventListener('click', function() {
            const questionId = this.dataset.question;
            const answersContainer = this.previousElementSibling;
            addAnswer(answersContainer, questionId);
        });
    });

    // Добавление ответа к новому вопросу
    function addNewAnswer(container, questionId) {
        const answerDiv = document.createElement('div');
        answerDiv.className = 'input-group mb-2 answer-item';
        answerDiv.innerHTML = `
            <input type="text" class="form-control" 
                   name="new_questions[${questionId}][answers][]" required>
            <button class="btn btn-outline-danger remove-answer" type="button">×</button>
        `;
        container.appendChild(answerDiv);
        
        answerDiv.querySelector('.remove-answer').addEventListener('click', function() {
            container.removeChild(answerDiv);
        });
    }

    // Удаление вопроса (AJAX)
    document.querySelectorAll('.remove-question').forEach(btn => {
        btn.addEventListener('click', function() {
            const questionId = this.dataset.id;
            if (confirm('Удалить этот вопрос?')) {
                fetch(`/poll/delete-question/${questionId}`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.closest('.question-item').remove();
                    } else {
                        alert('Ошибка при удалении вопроса');
                    }
                });
            }
        });
    });

    // Удаление ответа (AJAX)
    document.querySelectorAll('.remove-answer').forEach(btn => {
        if (btn.dataset.id) { // Только для существующих ответов
            btn.addEventListener('click', function() {
                const answerId = this.dataset.id;
                if (confirm('Удалить этот вариант ответа?')) {
                    fetch(`/poll/delete-answer/${answerId}`, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.closest('.answer-item').remove();
                        } else {
                            alert('Ошибка при удалении варианта ответа');
                        }
                    });
                }
            });
        }
    });
});
</script>