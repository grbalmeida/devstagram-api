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
}
