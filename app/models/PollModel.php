<?php
namespace models;

class PollModel extends Model
{

    public function createPoll($userId, $title, $status = 'draft')
    {
        $stmt = $this->db->prepare("INSERT INTO polls (user_id, title, status) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $title, $status]);
        return $this->db->lastInsertId();
    }

    public function getPolls($status = null)
    {
        $sql = "SELECT p.*, u.first_name, u.last_name FROM polls p JOIN users u ON p.user_id = u.id";
        if ($status) {
            $sql .= " WHERE p.status = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$status]);
        } else {
            $stmt = $this->db->query($sql);
        }
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getUserPolls($userId)
    {
        $stmt = $this->db->prepare("SELECT * FROM polls WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getPollWithQuestions($pollId)
    {
        // Получаем опрос
        $stmt = $this->db->prepare("SELECT * FROM polls WHERE id = ?");
        $stmt->execute([$pollId]);
        $poll = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$poll)
            return null;

        // Получаем вопросы
        $stmt = $this->db->prepare("SELECT * FROM questions WHERE poll_id = ?");
        $stmt->execute([$pollId]);
        $questions = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($questions as &$question) {
            $stmt = $this->db->prepare("SELECT * FROM answers WHERE question_id = ?");
            $stmt->execute([$question['id']]);
            $question['answers'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        $poll['questions'] = $questions;
        return $poll;
    }

    public function publishPoll($pollId, $userId)
    {
        $stmt = $this->db->prepare("UPDATE polls SET status = 'published' WHERE id = ? AND user_id = ?");
        return $stmt->execute([$pollId, $userId]);
    }

    public function updatePoll($pollId, $userId, $title, $status)
    {
        $stmt = $this->db->prepare("UPDATE polls SET title = ?, status = ? WHERE id = ? AND user_id = ?");
        return $stmt->execute([$title, $status, $pollId, $userId]);
    }

    public function deletePoll($pollId, $userId)
    {
        // Каскадное удаление благодаря FOREIGN KEY constraints
        $stmt = $this->db->prepare("DELETE FROM polls WHERE id = ? AND user_id = ?");
        return $stmt->execute([$pollId, $userId]);
    }

    public function addQuestion($pollId, $questionText)
    {
        $stmt = $this->db->prepare("INSERT INTO questions (poll_id, question_text) VALUES (?, ?)");
        $stmt->execute([$pollId, $questionText]);
        return $this->db->lastInsertId();
    }

    public function updateQuestion($questionId, $questionText)
    {
        $stmt = $this->db->prepare("UPDATE questions SET question_text = ? WHERE id = ?");
        return $stmt->execute([$questionText, $questionId]);
    }

    public function deleteQuestion($questionId)
    {
        $stmt = $this->db->prepare("DELETE FROM questions WHERE id = ?");
        return $stmt->execute([$questionId]);
    }

    public function addAnswer($questionId, $answerText)
    {
        $stmt = $this->db->prepare("INSERT INTO answers (question_id, answer_text) VALUES (?, ?)");
        $stmt->execute([$questionId, $answerText]);
        return $this->db->lastInsertId();
    }

    public function updateAnswer($answerId, $answerText)
    {
        $stmt = $this->db->prepare("UPDATE answers SET answer_text = ? WHERE id = ?");
        return $stmt->execute([$answerText, $answerId]);
    }

    public function deleteAnswer($answerId)
    {
        $stmt = $this->db->prepare("DELETE FROM answers WHERE id = ?");
        return $stmt->execute([$answerId]);
    }


    public function getRandomPublishedPoll()
    {
        try {
            // Получаем случайный опрос
            $stmt = $this->db->prepare("
            SELECT p.id, p.title 
            FROM polls p
            WHERE p.status = 'published'
            ORDER BY RAND() 
            LIMIT 1
        ");
            $stmt->execute();
            $poll = $stmt->fetch();

            if (!$poll)
                return null;

            // Получаем вопросы
            $poll['questions'] = $this->getQuestionsWithAnswers($poll['id']);

            return $poll;
        } catch (\PDOException $e) {
            throw new \Exception("Database error: " . $e->getMessage());
        }
    }

    private function getQuestionsWithAnswers($pollId)
    {
        $stmt = $this->db->prepare("
        SELECT q.id, q.question_text 
        FROM questions q
        WHERE q.poll_id = ?
    ");
        $stmt->execute([$pollId]);
        $questions = $stmt->fetchAll();

        foreach ($questions as &$question) {
            $question['answers'] = $this->getAnswersForQuestion($question['id']);
        }

        return $questions;
    }

    private function getAnswersForQuestion($questionId)
    {
        $stmt = $this->db->prepare("
        SELECT id, answer_text, votes 
        FROM answers 
        WHERE question_id = ?
    ");
        $stmt->execute([$questionId]);
        return $stmt->fetchAll();
    }
}