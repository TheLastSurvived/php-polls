<?php
namespace models;

class QuestionModel extends Model
{
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
}