<?php
namespace controllers;
use Exception;

class Controller
{

    protected $db;
    
    public function __construct()
    {
        // Инициализация подключения к БД
        $this->db = new \PDO('mysql:host=localhost;dbname=polls', 'root', '');
        $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }
    protected function render($view, $data = [])
    {
        extract($data);
        // Сохраняем путь к view-файлу в переменную
        $contentView = __DIR__ . '/../views/' . $view . '.php';

        // Проверяем существует ли файл
        if (!file_exists($contentView)) {
            throw new Exception("View file not found: " . $contentView);
        }

        // Передаем переменную в layout
        $layoutFile = __DIR__ . '/../views/layout.php';
        if (!file_exists($layoutFile)) {
            throw new Exception("Layout file not found");
        }

        require $layoutFile;
    }

    protected function setFlash($key, $message)
    {
        $_SESSION['flash'][$key] = $message;
    }

    protected function getFlash($key)
    {
        if (isset($_SESSION['flash'][$key])) {
            $message = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $message;
        }
        return null;
    }
}