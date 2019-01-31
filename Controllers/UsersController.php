<?php

namespace Controllers;

use \Core\Controller;
use \Models\User;

class UsersController extends Controller
{
   private $user;

   public function __construct()
   {
      $this->user = new User();
   }

   public function index(): void {}

   public function login(): void
   {
      $array = [];
      $method = $this->getMethod();
      $data = $this->getRequestData();

      if ($method === 'POST') {
         if (!empty($data['email']) && !empty($data['password'])) {
            if ($this->user->checkCredentials($data['email'], $data['password'])) {
               $array['jwt'] = $this->user->createJwt();
            } else {
               $array['error'] = 'Denied access';
            }
         } else {
            $array['error'] = 'Unidentified email or password';
         }
      } else {
         $array['error'] = 'Method not allowed';
      }

      $this->returnJson($array);
   }

   public function new_record(): void
   {
      $array = [];
      $method = $this->getMethod();
      $data = $this->getRequestData();

      if ($method === 'POST') {
         if (!empty($data['name']) && !empty($data['email']) && !empty($data['password'])) {
            if (filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
               if ($this->user->create($data['name'], $data['email'], $data['password'])) {
                  $array['jwt'] = $this->user->createJwt();
               } else {
                  $array['error'] = 'Email already exists';
               }
            } else {
               $array['error'] = 'Invalid email';
            }
         } else {
            $array['error'] = 'Information not available';
         }
      } else {
         $array['error'] = 'Method not allowed';
      }

      $this->returnJson($array);
   }

   public function view(int $id)
   {
      $array = ['logged' => false];
      $method = $this->getMethod();
      $data = $this->getRequestData();

      if (!empty($data['jwt']) && $this->user->validateJwt($data['jwt'])) {
         $array['logged'] = true;
         $array['is_me'] = false;

         if ($id === $this->user->getId()) {
            $array['is_me'] = true;
         }

         switch ($method) {
            case 'GET':

               break;
            case 'PUT':

               break;
            case 'DELETE':

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
