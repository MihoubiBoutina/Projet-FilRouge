<?php

namespace App\Controller\api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class DataController extends AbstractController
{
    #[Route('/data', name: 'get_data', methods: ['GET'])]
    public function getData(): JsonResponse
    {
        $data = [
            'id' => 1,
            'status' => 'success',
            'message' => 'This is your backend response'
        ];

        return $this->json($data);
    }
}