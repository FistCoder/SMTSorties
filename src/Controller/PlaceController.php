<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/place', name: 'places_')]
final class PlaceController extends AbstractController
{
    #[Route('/', name: 'list')]
    public function list(): Response
    {

    }

    #[Route('/add', name: 'add')]
    public function add(): Response
    {

    }

    #[Route('/modify/{id}', name: 'modify', requirements: ['id'=>'\d+'])]
    public function modify(int $id): Response
    {

    }

    #[Route('/delete/{id}', name: 'delete', requirements: ['id'=>'\d+'])]
    public function delete(int $id): Response
    {

    }
}
