<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user', name: 'user_')]
final class UserController extends AbstractController
{
    #[Route('/detail/{id}', name: 'detail')]
    public function userDetail(int $id): Response
    {
    }

    #[Route('/modify/{id}', name: 'modify')]
    public function userModify(int $id): Response
    {
    }
}
