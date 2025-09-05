<?php

namespace App\Controller;

use App\Repository\LocationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/places', name: 'places_')]
final class PlaceController extends AbstractController
{


    public function __construct(private readonly LocationRepository $locationRepository)
    {
    }

    #[Route('/', name: 'list')]
    public function list(): Response
    {
        $places = $this->locationRepository->findAll();

        return $this->render('places/list.html.twig',
        ['places' => $places]);
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
