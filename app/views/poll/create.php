<div class="container mt-4">
    <h1 class="mb-4">Создание нового опроса</h1>
    
    <form method="post">
        <div class="mb-3">
            <label for="title" class="form-label">Название опроса</label>
            <input type="text" class="form-control" id="title" name="title" required
                   value="<?= htmlspecialchars($title ?? '') ?>">
        </div>
        
        <div id="questions-container">
            <!-- Вопросы будут добавляться здесь -->
        </div>
        
        <div class="mb-3">
            <button type="button" class="btn btn-secondary" id="add-question">Добавить вопрос</button>
        </div>
        
        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary btn-lg">Сохранить опрос</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('questions-container');
    const addButton = document.getElementById('add-question');
    let questionCount = 0;
    
    function addQuestion() {
        const questionId = questionCount++;
        const questionDiv = document.createElement('div');
        questionDiv.className = 'card mb-3 question-item';
        questionDiv.innerHTML = `
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Вопрос #${questionId + 1}</h5>
                <button type="button" class="btn btn-sm btn-danger remove-question">Удалить</button>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Текст вопроса</label>
                    <input type="text" class="form-control" name="questions[]" required>
                </div>
                <div class="answers-container mb-3"></div>
                <button type="button" class="btn btn-sm btn-outline-secondary add-answer">Добавить вариант ответа</button>
            </div>
        `;
        
        container.appendChild(questionDiv);
        
        // Обработчики для кнопок внутри вопроса
        questionDiv.querySelector('.remove-question').addEventListener('click', function() {
            container.removeChild(questionDiv);
        });
        
        questionDiv.querySelector('.add-answer').addEventListener('click', function() {
            addAnswer(questionDiv.querySelector('.answers-container'));
        });
        
        // Добавляем 2 ответа по умолчанию
        const answersContainer = questionDiv.querySelector('.answers-container');
        addAnswer(answersContainer);
        addAnswer(answersContainer);
    }
    
    function addAnswer(container) {
        const answerDiv = document.createElement('div');
        answerDiv.className = 'input-group mb-2';
        answerDiv.innerHTML = `
            <input type="text" class="form-control" name="answers[${questionCount - 1}][]" required>
            <button class="btn btn-outline-danger remove-answer" type="button">×</button>
        `;
        container.appendChild(answerDiv);
        
        answerDiv.querySelector('.remove-answer').addEventListener('click', function() {
            container.removeChild(answerDiv);
        });
    }
    
    // Добавляем первый вопрос при загрузке
    addQuestion();
    
    // Обработчик кнопки добавления вопроса
    addButton.addEventListener('click', addQuestion);
});
</script>