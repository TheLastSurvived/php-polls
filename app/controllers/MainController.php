<?php
namespace controllers;

class MainController extends Controller
{
    public function actionIndex()
    {
        $user = $_SESSION['user'] ?? null;

        $data = [
            'title' => 'Главная страница',
            'content' => $user
                ? 'Добро пожаловать, ' . htmlspecialchars($user['first_name']) . '!'
                : 'Добро пожаловать на наш сайт!',
            'user' => $user
        ];

        $this->render('main', $data);
    }
}