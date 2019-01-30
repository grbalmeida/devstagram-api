<?php

namespace Core;

class Controller
{
   public function getMethod(): string
   {
      return $_SERVER['REQUEST_METHOD'];
   }

   public function getRequestData(): array
   {
      switch ($this->getMethod()) {
			case 'GET':
				return $_GET;
				break;
			case 'PUT':
			case 'DELETE':
				parse_str(file_get_contents('php://input'), $data);
				return (array) $data;
				break;
			case 'POST':
				$data = json_decode(parse_str(file_get_contents('php://input'), $data));

				if (is_null($data)) {
					$data = $_POST;
				}

				return (array) $data;
				break;
      }
   }

   public function returnJson(array $array): void
   {
		header('Content-Type: application/json');
		echo json_encode($array);
		exit;
   }
}
