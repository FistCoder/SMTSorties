<?php

namespace App\Controller;

use App\Entity\Hangout;
use App\Entity\User;
use App\Form\FilterHangoutType;
use App\Repository\CampusRepository;
use App\Repository\HangoutRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/hangouts', name: 'hangout_')]
final class HangoutController extends AbstractController
{


    public function __construct(private readonly HangoutRepository $hangoutRepository, private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route('/', name: 'list')]
    public function listHangouts(Request $request, CampusRepository $campusRepository): Response
    {
        //creation du form
        $filterForm = $this->createForm(FilterHangoutType::class);
        $filterForm->handleRequest($request);

        $isOrganizer = $filterForm->get('isOrganizer')->getData();
        $isRegistered = $filterForm->get('isRegistered')->getData();
        $isNotRegistered = $filterForm->get('isNotRegistered')->getData();
        $isPast = $filterForm->get('isPast')->getData();

        $filters['isOrganizer'] = $isOrganizer;
        $filters['isRegistered'] = $isRegistered;
        $filters['isNotRegistered'] = $isNotRegistered;
        $filters['isPast'] = $isPast;

        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $filters = $filterForm->getData();

        } else {
            $filters = [];
        }


        //Appel du repo et transmition de l'utilisateur connecter
        $user = $this->getUser();

        if (!$user) {
            // Gère le cas utilisateur non connecté (redirige, exception, etc.)
            throw $this->createAccessDeniedException('Vous devez être connecté');
        }
        $hangouts = $this->hangoutRepository->findFilteredEvent($user, $filters);


        dump($filters, $hangouts);

        return $this->render('hangouts/list.html.twig',
            ['hangouts' => $hangouts,
                'filterForm' => $filterForm]);
    }

//    #[Route('/add', name: 'add')]
//    public function addHangout(): Response
//    {
//    }
//
//    #[Route('/modify/{id}', name: 'modify', requirements: ['id'=>'\d+'])]
//    public function modifyHangout(int $id): Response
//    {
//    }
//
//    #[Route('/delete/{id}', name: 'delete', requirements: ['id'=>'\d+'])]
//    public function deleteHangout(int $id): Response
//    {
//    }
//
//    #[Route('/cancel/{id}', name: 'cancel', requirements: ['id'=>'\d+'])]
//    public function cancelHangout(int $id): Response
//    {
//    }
//
//    #[Route('/subscribe/{id}', name: 'subscribe', requirements: ['id'=>'\d+'])]
//    public function subscribeToHangout(): Response
//    {
//    }
//
//    #[Route('/unsubscribe/{id}', name: 'unsubscribe', requirements: ['id'=>'\d+'])]
//    public function unsubscribeFromHangout(): Response
//    {
//    }
}
