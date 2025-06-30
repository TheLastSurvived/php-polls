<?php
namespace models;

class UserModel extends Model
{
    protected $table = 'users';
    public function getUsers()
    {
        $stmt = $this->db->query("SELECT * FROM users");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }


    public function create($data)
    {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
        $stmt->execute([$data['first_name'], $data['last_name'], $data['email'], password_hash($data['password'], PASSWORD_BCRYPT)]);
        return $this->db->lastInsertId();
    }

    public function findByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function verifyPassword($email, $password)
    {
        $user = $this->findByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }
}