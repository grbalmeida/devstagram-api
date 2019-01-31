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
}
