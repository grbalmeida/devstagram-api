<?php

namespace Controllers;

use \Core\Controller;
use \Models\User;
use \Models\Photo;

class PhotosController extends Controller
{
   private $user;
   private $photo;

   public function __construct()
   {
      $this->user = new User();
      $this->photo = new Photo();
   }

   public function index() { }

   public function random(): void
   {
      $array = ['logged' => false];
      $method = $this->getMethod();
      $data = $this->getRequestData();

      if (!empty($data['jwt']) && $this->user->validateJwt($data['jwt'])) {
         $array['logged'] = true;

         if ($method === 'GET') {
            $per_page = 10;

            if (!empty($data['per_page'])) $per_page = intval($data['per_page']);
            $excludes = [];

            if (!empty($data['excludes'])) $excludes = implode(',', $data['excludes']);
            $array['data'] = $this->photo->getRandomPhotos($per_page);
         } else {
            $array['error'] = 'Method not allowed';
         }

      } else {
         $array['error'] = 'Access denied';
      }

      $this->returnJson($array);
   }

   public function view(int $id): void
   {
      $array = ['logged' => false];
      $method = $this->getMethod();
      $data = $this->getRequestData();

      if (!empty($data['jwt']) && $this->user->validateJwt($data['jwt'])) {
         $array['logged'] = true;

         switch ($method) {
            case 'GET':
               $array['data'] = $this->photo->getPhoto($id);
               break;
            case 'DELETE':
               $info = $this->photo->deletePhoto($id, $this->user->getId());
               if ($info !== '') $array['error'] = $info;
               break;
            default:
               $array['error'] = 'Method not allowed';
         }
      } else {
         $array['error'] = 'Access denied';
      }

      $this->returnJson($array);
   }

   public function comment(int $id): void
   {
      $array = ['logged' => false];
      $method = $this->getMethod();
      $data = $this->getRequestData();

      if (!empty($data['jwt']) && $this->user->validateJwt($data['jwt'])) {
         $array['logged'] = true;

         switch ($method) {
            case 'POST':
               if (!empty($data['comment'])) {
                  $info = $this->photo->addComment($id, $this->user->getId(), $data['comment']);
                  if ($info !== '') $array['error'] = $info;
               } else {
                  $array['error'] = 'The comment field can not be empty';
               }
               break;
            default:
               $array['error'] = 'Method not allowed';
         }
      } else {
         $array['error'] = 'Access denied';
      }

      $this->returnJson($array);
   }

   public function deleteComment(int $id): void
   {
      $array = ['logged' => false];
      $method = $this->getMethod();
      $data = $this->getRequestData();

      if (!empty($data['jwt']) && $this->user->validateJwt($data['jwt'])) {
         $array['logged'] = true;

         switch ($method) {
            case 'DELETE':
               $info = $this->photo->deleteComment($id, $this->user->getId());
               if ($info !== '') $array['error'] = $info;
               break;
            default:
               $array['error'] = 'Method not allowed';
         }
      } else {
         $array['error'] = 'Access denied';
      }

      $this->returnJson($array);
   }
}
