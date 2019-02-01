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
      $sql = 'DELETE FROM photos WHERE user_id = :id';
      $sql = $this->database->prepare($sql);
      $sql->bindParam(':id', $id);
      $sql->execute();

      $sql = 'DELETE FROM photos_has_comments WHERE user_id = :id';
      $sql = $this->database->prepare($sql);
      $sql->bindParam(':id', $id);
      $sql->execute();

      $sql = 'DELETE FROM photos_has_likes WHERE user_id = :id';
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
               $array[$key]['url'] = BASE_URL.'/assets/images/avatar/'.$value['url'];
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
}
