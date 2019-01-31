<?php

namespace Models;

use \Core\Model;

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
}
