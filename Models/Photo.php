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
}
