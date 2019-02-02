<?php

namespace Models;

use \Core\Model;
use \Models\User;

class Photo extends Model
{
   public function __construct()
   {
      parent::__construct();
   }

   public function getRandomPhotos(int $per_page, array $excludes = []): array
   {
      $array = [];

      $excludes = array_map(function($value) {
         return intval($value);
      }, $excludes);

      $sql = 'SELECT id, user_id, url FROM photos ';

      if (count($excludes) > 0) $sql .= 'WHERE id NOT IN('.implode(',', $excludes).') ';

      $sql .= 'ORDER BY RAND() LIMIT '.$per_page;
      $sql = $this->database->prepare($sql);

      $sql->execute();

      if ($sql->rowCount() > 0) {
         $array = $sql->fetchAll(\PDO::FETCH_ASSOC);

         foreach ($array as $key => $value) {
            $array[$key]['url'] = BASE_URL.'/assets/images/photos/'.$value['url'];
            $array[$key]['like_count'] = $this->getLikeCount($value['id']);
            $array[$key]['comments'] = $this->getComments($value['id']);
         }
      }

      return $array;
   }

   public function deletePhoto(int $photo_id, int $user_id): string
   {
      $sql = 'SELECT id FROM photos WHERE id = :photo_id AND user_id = :user_id';
      $sql = $this->database->prepare($sql);
      $sql->bindParam(':photo_id', $photo_id);
      $sql->bindParam(':user_id', $user_id);
      $sql->execute();

      if ($sql->rowCount() > 0) {
         $sql = 'DELETE FROM photos_has_comments WHERE photo_id = :id';
         $sql = $this->database->prepare($sql);
         $sql->bindParam(':id', $photo_id);
         $sql->execute();

         $sql = 'DELETE FROM photos_has_likes WHERE photo_id = :id';
         $sql = $this->database->prepare($sql);
         $sql->bindParam(':id', $photo_id);
         $sql->execute();

         $sql = 'DELETE FROM photos WHERE id = :id';
         $sql = $this->database->prepare($sql);
         $sql->bindParam(':id', $photo_id);
         $sql->execute();

         return '';
      }

      return 'You can not delete this photo';
   }

   public function getPhotosCount(int $id): int
   {
      $sql = 'SELECT COUNT(*) AS count FROM photos WHERE user_id = :id';
      $sql = $this->database->prepare($sql);
      $sql->bindValue(':id', $id);
      $sql->execute();

      return $sql->fetch(\PDO::FETCH_ASSOC)['count'];
   }

   public function deleteAll(int $id): void
   {
      $sql = 'DELETE FROM photos_has_comments WHERE user_id = :id';
      $sql = $this->database->prepare($sql);
      $sql->bindParam(':id', $id);
      $sql->execute();

      $sql = 'DELETE FROM photos_has_likes WHERE user_id = :id';
      $sql = $this->database->prepare($sql);
      $sql->bindParam(':id', $id);
      $sql->execute();

      $sql = 'DELETE FROM photos WHERE user_id = :id';
      $sql = $this->database->prepare($sql);
      $sql->bindParam(':id', $id);
      $sql->execute();
   }

   public function getFeedCollection(array $following, int $offset, int $per_page): array
   {
      $array = [];

      if (count($following) > 0) {
         $sql = "SELECT id, user_id, url
                  FROM photos
                  WHERE user_id
                  IN(:following)
                  ORDER BY id DESC
                  LIMIT ".$offset.", ".$per_page."";
         $sql = $this->database->prepare($sql);
         $sql->bindValue(':following', implode(',', $following));
         $sql->execute();

         if ($sql->rowCount() > 0) {
            $array = $sql->fetchAll(\PDO::FETCH_ASSOC);

            $userModel = new User();

            foreach ($array as $key => $value) {
               $user = $userModel->getInfo($value['user_id']);
               $array[$key]['name'] = $user['name'];
               $array[$key]['avatar'] = $user['avatar'];
               $array[$key]['url'] = BASE_URL.'/assets/images/photos/'.$value['url'];
               $array[$key]['like_count'] = $this->getLikeCount($value['id']);
               $array[$key]['comments'] = $this->getComments($value['id']);
            }
         }
      }
      return $array;
   }

   public function getLikeCount(int $id): int
   {
      $sql = 'SELECT COUNT(*) AS count FROM photos_has_likes WHERE photo_id = :id';
      $sql = $this->database->prepare($sql);
      $sql->bindValue(':id', $id);
      $sql->execute();

      return $sql->fetch(\PDO::FETCH_ASSOC)['count'];
   }

   public function getPhoto(int $id): array
   {
      $array = [];

      $sql = 'SELECT id, user_id, url FROM photos WHERE id = :id';
      $sql = $this->database->prepare($sql);
      $sql->bindValue(':id', $id);
      $sql->execute();

      if ($sql->rowCount() > 0) {
         $array = $sql->fetch(\PDO::FETCH_ASSOC);
         $userModel = new User();
         $user = $userModel->getInfo($array['user_id']);
         $array['name'] = $user['name'];
         $array['avatar'] = $user['avatar'];
         $array['url'] = BASE_URL.'/assets/images/photos/'.$array['url'];
         $array['like_count'] = $this->getLikeCount($array['id']);
         $array['comments'] = $this->getComments($array['id']);
      }

      return $array;
   }

   public function getComments(int $id): array
   {
      $array = [];

      $sql = 'SELECT p.*, users.name
               FROM photos_has_comments p
               LEFT JOIN users
               ON users.id = p.user_id
               WHERE photo_id = :id';
      $sql = $this->database->prepare($sql);
      $sql->bindValue(':id', $id);
      $sql->execute();

      if ($sql->rowCount() > 0) {
         $array = $sql->fetchAll(\PDO::FETCH_ASSOC);
      }

      return $array;
   }

   public function getPhotosFromUser(int $id, int $offset, int $per_page): array
   {
      $array = [];

      $sql = "SELECT * FROM photos
               WHERE user_id = :id
               ORDER BY id DESC
               LIMIT ".$offset.", ".$per_page;
      $sql = $this->database->prepare($sql);
      $sql->bindValue(':id', $id);
      $sql->execute();

      if ($sql->rowCount() > 0) {
         $array = $sql->fetchAll(\PDO::FETCH_ASSOC);

         foreach ($array as $key => $value) {
            $array[$key]['url'] = BASE_URL.'/assets/images/photos/'.$value['url'];
            $array[$key]['like_count'] = $this->getLikeCount($value['id']);
            $array[$key]['comments'] = $this->getComments($value['id']);
         }
      }

      return $array;
   }

   public function addComment(int $photo_id, int $user_id, string $comment): string
   {
      $sql = 'SELECT COUNT(*) AS count FROM photos WHERE id = :id';
      $sql = $this->database->prepare($sql);
      $sql->bindValue(':id', $photo_id);
      $sql->execute();

      if ($sql->fetch(\PDO::FETCH_ASSOC)['count'] > 0) {
         $sql = 'INSERT INTO photos_has_comments(user_id, photo_id, created_at, comment)
               VALUES (:user_id, :photo_id, NOW(), :comment)';
         $sql = $this->database->prepare($sql);
         $sql->bindValue(':user_id', $user_id);
         $sql->bindValue(':photo_id', $photo_id);
         $sql->bindValue(':comment', $comment);
         $sql->execute();
         return '';
      }

      return 'This photo does not exist';
   }
}
