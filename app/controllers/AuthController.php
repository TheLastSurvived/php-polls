<?php
namespace controllers;

use models\UserModel;

class AuthController extends Controller
{
    public function actionRegister()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'first_name' => $_POST['first_name'] ?? '',
                'last_name' => $_POST['last_name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'password' => $_POST['password'] ?? '',
                'confirm_password' => $_POST['confirm_password'] ?? ''
            ];

            // Валидация
            $errors = $this->validateRegisterData($data);

            if (empty($errors)) {
                $userModel = new UserModel();

                // Проверяем, нет ли уже пользователя с таким email
                if ($userModel->findByEmail($data['email'])) {
                    $errors['email'] = 'Пользователь с таким email уже существует';
                } else {
                    // Создаем пользователя
                    $userId = $userModel->create($data);

                    // Автоматически логиним пользователя после регистрации
                    $_SESSION['user'] = $userModel->findByEmail($data['email']);

                    // Перенаправляем на главную
                    header('Location: /');
                    // После успешной регистрации
                    $this->setFlash('success', 'Регистрация прошла успешно!');
                    exit;
                }
            }

            $this->render('auth/register', ['errors' => $errors, 'data' => $data]);
            // После ошибки
            $this->setFlash('error', 'Ошибка при регистрации');
        } else {
            $this->render('auth/register');
        }
    }

    public function actionLogin()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $userModel = new UserModel();
            $user = $userModel->verifyPassword($email, $password);

            if ($user) {
                $_SESSION['user'] = $user;
                header('Location: /');
                exit;
            } else {
                $this->render('auth/login', ['error' => 'Неверный email или пароль']);
            }
        } else {
            $this->render('auth/login');
        }
    }

    public function actionLogout()
    {
        unset($_SESSION['user']);
        session_destroy();
        header('Location: /');
        exit;
    }

    protected function validateRegisterData($data)
    {
        $errors = [];

        if (empty($data['first_name'])) {
            $errors['first_name'] = 'Имя обязательно';
        }

        if (empty($data['last_name'])) {
            $errors['last_name'] = 'Фамилия обязательна';
        }

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Некорректный email';
        }

        if (empty($data['password']) || strlen($data['password']) < 6) {
            $errors['password'] = 'Пароль должен содержать минимум 6 символов';
        }

        if ($data['password'] !== $data['confirm_password']) {
            $errors['confirm_password'] = 'Пароли не совпадают';
        }

        return $errors;
    }
}