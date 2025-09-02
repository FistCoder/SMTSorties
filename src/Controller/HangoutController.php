<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/hangouts', name: 'hangout_')]
final class HangoutController extends AbstractController
{
    #[Route('/', name: 'list')]
    public function listHangouts(): Response
    {
    }

    #[Route('/add', name: 'add')]
    public function addHangout(): Response
    {
    }

    #[Route('/modify/{id}', name: 'modify', requirements: ['id'=>'\d+'])]
    public function modifyHangout(int $id): Response
    {
    }

    #[Route('/delete/{id}', name: 'delete', requirements: ['id'=>'\d+'])]
    public function deleteHangout(int $id): Response
    {
    }

    #[Route('/cancel/{id}', name: 'cancel', requirements: ['id'=>'\d+'])]
    public function cancelHangout(int $id): Response
    {
    }

    #[Route('/subscribe/{id}', name: 'subscribe', requirements: ['id'=>'\d+'])]
    public function subscribeToHangout(): Response
    {
    }

    #[Route('/unsubscribe/{id}', name: 'unsubscribe', requirements: ['id'=>'\d+'])]
    public function unsubscribeFromHangout(): Response
    {
    }
}
