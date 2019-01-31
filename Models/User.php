<?php

namespace Models;

use \Core\Model;
use \Models\Jwt;
use \Models\Photo;

class User extends Model
{
   private $id;
   private $photo;

   public function __construct()
   {
      parent::__construct();
      $this->photo = new Photo();
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

   public function getId(): int
   {
      return $this->id;
   }

   public function getInfo(int $id): array
   {
      $array = [];

      $sql = 'SELECT id, name, email, avatar FROM users WHERE id = :id';
      $sql = $this->database->prepare($sql);
      $sql->bindValue(':id', $id);
      $sql->execute();

      if ($sql->rowCount() > 0) {
         $array = $sql->fetch(\PDO::FETCH_ASSOC);

         if (!empty($array['avatar'])) {
            $array['avatar'] = BASE_URL.'/media/avatar/'.$array['avatar'];
         } else {
            $array['avatar'] = BASE_URL.'/media/avatar/default.jpg';
         }

         $array['following'] = $this->getFollowingCount($id);
         $array['followers'] = $this->getFollowersCount($id);
         $array['photos_count'] = $this->photo->getPhotosCount($id);
      }

      return $array;
   }

   public function getFollowingCount(int $id): int
   {
      $sql = 'SELECT COUNT(*) AS count FROM followers WHERE first_user = :id';
      $sql = $this->database->prepare($sql);
      $sql->bindValue(':id', $id);
      $sql->execute();

      return $sql->fetch(\PDO::FETCH_ASSOC)['count'];
   }

   public function getFollowersCount(int $id): int
   {
      $sql = 'SELECT COUNT(*) AS count FROM followers WHERE second_user = :id';
      $sql = $this->database->prepare($sql);
      $sql->bindValue(':id', $id);
      $sql->execute();

      return $sql->fetch(\PDO::FETCH_ASSOC)['count'];
   }

   public function createJwt(): string
   {
      $jwt = new Jwt();
      return $jwt->create(['user_id' => $this->id]);
   }

   public function validateJwt(string $token): bool
   {
      $jwt = new Jwt();
      $info = $jwt->validate($token);

      if (isset($info->user_id)) {
         $this->id = $info->user_id;
         return true;
      }

      return false;
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

   public function edit(int $id, array $data): string
   {
      if ($id === $this->getId()) {
         $toChange = [];

         if (!empty($data['name'])) $toChange['name'] = $data['name'];
         if (!empty($data['email'])) {
            if (filter_var($data['email'], FILTER_VALIDATE_EMAIL)
            && !$this->emailExists($data['email'])) {
               $toChange['email'] = $data['email'];
            } else {
               return 'Invalid or already registered email';
            }
         }

         if (!empty($data['password']))
            $toChange['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

         if (count($toChange) > 0) {
            $fields = [];

            foreach ($toChange as $key => $value) {
               $fields[] = $key. ' = :'.$key;
            }

            $sql = 'UPDATE users SET '.implode(',', $fields).' WHERE id = :id';
            $sql = $this->database->prepare($sql);
            $sql->bindValue(':id', $id);

            foreach ($toChange as $key => $value) {
               $sql->bindValue(':'.$key, $value);
            }

            $sql->execute();
         } else {
            return 'Fill in the data correctly';
         }

         return '';
      }

      return 'It is not allowed to edit another user';
   }

   public function delete(int $id): string
   {
      if ($id === $this->getId()) {
         // ON DELETE CASCADE
         $this->photo->deleteAll($id);

         $sql = 'DELETE FROM followers WHERE second_user = :id OR first_user = :id';
         $sql = $this->database->prepare($sql);
         $sql->bindParam(':id', $id);
         $sql->execute();

         $sql = 'DELETE FROM users WHERE id = :id';
         $sql = $this->database->prepare($sql);
         $sql->bindParam(':id', $id);
         $sql->execute();

         return '';
      }

      return 'Deleting another user is not allowed';
   }
}
