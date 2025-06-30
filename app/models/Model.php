<?php
namespace models;

class Model
{
    protected $db;
    
    public function __construct()
    {
        // Инициализация подключения к БД
        $this->db = new \PDO('mysql:host=localhost;dbname=polls', 'root', '');
    }
}