<?php
namespace controllers;

use models\PollModel;

class ApiController
{
    protected $db;
    
    public function __construct()
    {
        header('Content-Type: application/json');
        $this->db = new \PDO('mysql:host=localhost;dbname=polls', 'root', '');
    }

    public function actionRandomPoll()
    {
        try {
            $pollModel = new PollModel();
            $poll = $pollModel->getRandomPublishedPoll();
            
            if (!$poll) {
                http_response_code(404);
                echo json_encode(['error' => 'No published polls available']);
                return;
            }
            
            echo json_encode([
                'id' => $poll['id'],
                'title' => $poll['title'],
                'questions' => array_map(function($question) {
                    return [
                        'id' => $question['id'],
                        'text' => $question['question_text'],
                        'answers' => array_map(function($answer) {
                            return [
                                'id' => $answer['id'],
                                'text' => $answer['answer_text'],
                                'votes' => $answer['votes']
                            ];
                        }, $question['answers'])
                    ];
                }, $poll['questions'])
            ]);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}