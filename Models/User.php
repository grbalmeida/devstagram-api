<?php

namespace Models;

use \Core\Model;
use \Models\Jwt;

class User extends Model
{
   private $id;
   private $jwt;

   public function __construct()
   {
      parent::__construct();
      $this->jwt = new Jwt();
   }

   public function create(string $name, string $email, string $password): bool
   {
      if (!$this->emailExists($email)) {
         $hash = password_hash($password, PASSWORD_DEFAULT);
         $sql = 'INSERT INTO users (name, email, password) VALUES (:name, :email, :password)';
         $sql = $this->database->prepare($sql);
         $sql->bindValue(':name', $name);
         $sql->bindValue(':email', $email);
         $sql->bindValue(':password', $hash);
         $sql->execute();
         $this->id = $this->database->lastInsertId();
         return true;
      }

      return false;
   }

   public function checkCredentials(string $email, string $password): bool
   {
      $sql = 'SELECT id, password FROM users WHERE email = :email';
      $sql = $this->database->prepare($sql);
      $sql->bindValue(':email', $email);
      $sql->execute();

      if ($sql->rowCount() > 0) {
         $info = $sql->fetch(\PDO::FETCH_ASSOC);

         if (password_verify($password, $info['password'])) {
            $this->id = $info['id'];
            return true;
         }
      }

      return false;
   }

   public function createJwt(): string
   {
      return $this->jwt->create(['user_id' => $this->id]);
   }

   private function emailExists(string $email): bool
   {
      $sql = 'SELECT id FROM users WHERE email = :email';
      $sql = $this->database->prepare($sql);
      $sql->bindValue(':email', $email);
      $sql->execute();

      if ($sql->rowCount() > 0) {
         return true;
      }

      return false;
   }
}
