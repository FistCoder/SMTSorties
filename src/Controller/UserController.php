<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user', name: 'user_')]
final class UserController extends AbstractController
{
    #[Route('/detail/{id}', name: 'detail', requirements: ['id'=>'\d+'])]
    public function userDetail(int $id): Response
    {
    }

    #[Route('/modify/{id}', name: 'modify', requirements: ['id'=>'\d+'])]
    public function userModify(int $id): Response
    {
    }
}
