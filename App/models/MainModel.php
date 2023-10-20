<?php
namespace App\models;
use App\core\Model;

class MainModel extends Model
{
    // получение данных пользователя по логину
    public function getUser($login)
    {
        $sql = "SELECT * FROM users WHERE email = :email";   

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":email", $login, \PDO::PARAM_STR);
        $stmt->execute();
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        $user = $res;
        return $user;        
    }

    // получение инфы о наличии такого пользователя
    public function checkUser($login)
    {              
        $user = $this->getUser($login)['email'];

        if($user === $login) {
            return true;
        } else {            
            return false;
        }
    }

    // Регистрация пользователя
    public function regUser($login, $password, $role)
    {
        $password_hash = password_hash($password, PASSWORD_ARGON2ID);

        $sql = "INSERT INTO users (email, password, role)
                VALUES (:email, :password, :role)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':email', $login, \PDO::PARAM_STR);
        $stmt->bindParam(':password', $password_hash, \PDO::PARAM_STR);
        $stmt->bindParam(':role', $role, \PDO::PARAM_STR);      

        if(!$this->checkUser($login)){            
            $stmt->execute();
            $_SESSION['message'] = "Регистрация прошла успешно!";
            header("Location: /");            
        } else {
            $_SESSION['message'] = "Такой пользователь уже существует!";
            header("Location: /");
        }
    }

    // Вход пользователя
    public function loginUser($login, $password)
    {
        $user = $this->getUser($login);
        $userPass = $user['password'];        
        if (empty($user) || empty($userPass) || !password_verify($password, $userPass)) {            
            $_SESSION['message'] = "Неверно указан логин или пароль!";            
            header("Location: /");      
        } elseif (password_verify($password, $userPass) && intval($user['banned']) === 1) {
            $_SESSION['message'] = "Ваш аккаунт заблокирован!";     
            header("Location: /");
        } elseif (password_verify($password, $userPass) && intval($user['banned']) === 0) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            header("Location: /Board");
        }
    }   
}