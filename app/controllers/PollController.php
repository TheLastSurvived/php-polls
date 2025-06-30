<?php
namespace controllers;

use models\PollModel;
use models\QuestionModel;
use models\AnswerModel;
use Exception;


class PollController extends Controller
{
    public function actionIndex()
    {
        $pollModel = new PollModel();
        $polls = $pollModel->getPolls('published');

        $this->render('poll/index', [
            'polls' => $polls,
            'user' => $_SESSION['user'] ?? null
        ]);
    }

    public function actionView($pollId)
    {
        $pollModel = new PollModel();
        $poll = $pollModel->getPollWithQuestions($pollId);

        if (!$poll || $poll['status'] !== 'published') {
            $this->setFlash('error', 'Опрос не найден или не опубликован');
            header('Location: /');
            exit;
        }

        $this->render('poll/view', [
            'poll' => $poll,
            'user' => $_SESSION['user'] ?? null
        ]);
    }

    public function actionVote()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /');
            exit;
        }

        $pollId = $_POST['poll_id'] ?? null;
        $answers = $_POST['answers'] ?? [];

        if (!$pollId || empty($answers)) {
            header("Location: /poll/view/$pollId");
            exit;
        }

        $answerModel = new AnswerModel();

        foreach ($answers as $questionId => $answerId) {
            $answerModel->vote($answerId);
        }

        header("Location: /poll/view/$pollId");
        exit;
    }

    public function actionMyPolls()
    {
        if (!isset($_SESSION['user'])) {
            $this->setFlash('error', 'Необходимо авторизоваться');
            header('Location: /auth/login');
            exit;
        }

        $pollModel = new PollModel();
        $polls = $pollModel->getUserPolls($_SESSION['user']['id']);

        $this->render('poll/my', [
            'polls' => $polls,
            'user' => $_SESSION['user']
        ]);
    }

    public function actionCreate()
    {
        if (!isset($_SESSION['user'])) {
            $this->setFlash('error', 'Необходимо авторизоваться');
            header('Location: /auth/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'] ?? '';
            $questions = $_POST['questions'] ?? [];
            $answers = $_POST['answers'] ?? [];

            if (empty($title) || empty($questions)) {
                $this->setFlash('error', 'Заполните все обязательные поля');
                $this->render('poll/create', [
                    'title' => $title,
                    'questions' => $questions,
                    'answers' => $answers,
                    'user' => $_SESSION['user']
                ]);
                return;
            }

            $pollModel = new PollModel();
            $questionModel = new QuestionModel();
            $answerModel = new AnswerModel();

            // Создаем опрос
            $pollId = $pollModel->createPoll($_SESSION['user']['id'], $title, 'draft');

            // Добавляем вопросы и ответы
            foreach ($questions as $qIndex => $questionText) {
                if (empty($questionText))
                    continue;

                $questionId = $questionModel->addQuestion($pollId, $questionText);

                if (!empty($answers[$qIndex])) {
                    foreach ($answers[$qIndex] as $answerText) {
                        if (!empty($answerText)) {
                            $answerModel->addAnswer($questionId, $answerText);
                        }
                    }
                }
            }

            $this->setFlash('success', 'Опрос создан! Вы можете опубликовать его в личном кабинете.');
            header('Location: /poll/my');
            exit;
        }

        $this->render('poll/create', ['user' => $_SESSION['user']]);
    }

    public function actionPublish($pollId)
    {
        if (!isset($_SESSION['user'])) {
            $this->setFlash('error', 'Необходимо авторизоваться');
            header('Location: /auth/login');
            exit;
        }

        $pollModel = new PollModel();
        $success = $pollModel->publishPoll($pollId, $_SESSION['user']['id']);

        if ($success) {
            $this->setFlash('success', 'Опрос опубликован!');
        } else {
            $this->setFlash('error', 'Не удалось опубликовать опрос');
        }

        header('Location: /poll/my');
        exit;
    }

    public function actionEdit($pollId)
    {
        if (!isset($_SESSION['user'])) {
            $this->setFlash('error', 'Необходимо авторизоваться');
            header('Location: /auth/login');
            exit;
        }

        $pollModel = new PollModel();
        $poll = $pollModel->getPollWithQuestions($pollId);

        // Проверяем, что опрос принадлежит текущему пользователю
        if (!$poll || $poll['user_id'] != $_SESSION['user']['id']) {
            $this->setFlash('error', 'У вас нет прав для редактирования этого опроса');
            header('Location: /poll/my');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'] ?? '';
            $status = $_POST['status'] ?? 'draft';
            $questions = $_POST['questions'] ?? [];
            $answers = $_POST['answers'] ?? [];

            try {
                // Обновляем основной опрос
                $pollModel->updatePoll($pollId, $_SESSION['user']['id'], $title, $status);

                // Обновляем вопросы и ответы
                foreach ($questions as $questionId => $questionText) {
                    $pollModel->updateQuestion($questionId, $questionText);
                }

                foreach ($answers as $answerId => $answerText) {
                    $pollModel->updateAnswer($answerId, $answerText);
                }

                // Добавляем новые вопросы, если есть
                if (!empty($_POST['new_questions'])) {
                    foreach ($_POST['new_questions'] as $newQuestion) {
                        if (!empty($newQuestion['text'])) {
                            $questionId = $pollModel->addQuestion($pollId, $newQuestion['text']);

                            if (!empty($newQuestion['answers'])) {
                                foreach ($newQuestion['answers'] as $answerText) {
                                    if (!empty($answerText)) {
                                        $pollModel->addAnswer($questionId, $answerText);
                                    }
                                }
                            }
                        }
                    }
                }

                $this->setFlash('success', 'Опрос успешно обновлен!');
                header("Location: /poll/edit/$pollId");
                exit;
            } catch (Exception $e) {
                $this->setFlash('error', 'Ошибка при обновлении опроса: ' . $e->getMessage());
            }
        }

        $this->render('poll/edit', [
            'poll' => $poll,
            'user' => $_SESSION['user']
        ]);
    }

    public function actionDelete($pollId)
    {
        if (!isset($_SESSION['user'])) {
            $this->setFlash('error', 'Необходимо авторизоваться');
            header('Location: /auth/login');
            exit;
        }

        $pollModel = new PollModel();
        $success = $pollModel->deletePoll($pollId, $_SESSION['user']['id']);

        if ($success) {
            $this->setFlash('success', 'Опрос успешно удален');
        } else {
            $this->setFlash('error', 'Не удалось удалить опрос или у вас нет прав');
        }

        header('Location: /poll/my');
        exit;
    }

    public function actionDeleteQuestion($questionId)
    {
        if (!isset($_SESSION['user']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
            header('HTTP/1.1 403 Forbidden');
            exit;
        }

        try {
            $pollModel = new PollModel();
            // Проверяем, что вопрос принадлежит пользователю
            $stmt = $this->db->prepare("SELECT p.user_id FROM questions q 
                                  JOIN polls p ON q.poll_id = p.id 
                                  WHERE q.id = ?");
            $stmt->execute([$questionId]);
            $poll = $stmt->fetch();

            if (!$poll || $poll['user_id'] != $_SESSION['user']['id']) {
                header('HTTP/1.1 403 Forbidden');
                exit;
            }

            $success = $pollModel->deleteQuestion($questionId);

            header('Content-Type: application/json');
            echo json_encode(['success' => $success]);
        } catch (Exception $e) {
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }

    public function actionDeleteAnswer($answerId)
    {
        if (!isset($_SESSION['user']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
            header('HTTP/1.1 403 Forbidden');
            exit;
        }

        try {
            $pollModel = new PollModel();
            // Проверяем, что ответ принадлежит пользователю
            $stmt = $this->db->prepare("SELECT p.user_id FROM answers a 
                                  JOIN questions q ON a.question_id = q.id 
                                  JOIN polls p ON q.poll_id = p.id 
                                  WHERE a.id = ?");
            $stmt->execute([$answerId]);
            $poll = $stmt->fetch();

            if (!$poll || $poll['user_id'] != $_SESSION['user']['id']) {
                header('HTTP/1.1 403 Forbidden');
                exit;
            }

            $success = $pollModel->deleteAnswer($answerId);

            header('Content-Type: application/json');
            echo json_encode(['success' => $success]);
        } catch (Exception $e) {
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }
}