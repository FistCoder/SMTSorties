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

    #[Route('/modify/{id}', name: 'modify')]
    public function modifyHangout(int $id): Response
    {
    }

    #[Route('/delete/{id}', name: 'delete')]
    public function deleteHangout(int $id): Response
    {
    }

    #[Route('/cancel/{id}', name: 'cancel')]
    public function cancelHangout(int $id): Response
    {
    }

    #[Route('/subscribe/{id}', name: 'subscribe')]
    public function subscribeToHangout(): Response
    {
    }

    #[Route('/unsubscribe/{id}', name: 'unsubscribe')]
    public function unsubscribeFromHangout(): Response
    {
    }
}
