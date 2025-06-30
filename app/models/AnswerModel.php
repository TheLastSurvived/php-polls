<?php
namespace models;
use Exception;

class AnswerModel extends Model
{
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

    public function vote($answerId)
{
    // Просто добавляем голос без проверок
    $stmt = $this->db->prepare("UPDATE answers SET votes = votes + 1 WHERE id = ?");
    $stmt->execute([$answerId]);
    
    // Записываем факт голосования (без привязки к пользователю)
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt = $this->db->prepare("INSERT INTO user_votes (answer_id, ip_address) VALUES (?, ?)");
    $stmt->execute([$answerId, $ip]);
    
    return true;
}
}