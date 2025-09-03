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
    public function listHangouts(Request $request): Response
    {

        $user = $this->getUser();

        if (!$user) {
            // Gère le cas utilisateur non connecté (redirige, exception, etc.)
            throw $this->createAccessDeniedException('Vous devez être connecté');
        }

        //creation du form
        $filterForm = $this->createForm(FilterHangoutType::class);
        $filterForm->handleRequest($request);


//recuperation des donées du formulaire de filtres remplis et ajout de ces données dans le tableau de filtre qui seras envoyer au repository
        $filters = [];

        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $filters = $filterForm->getData();

            // Récupération des champs non mappés
            $filters['isOrganizer'] = $filterForm->get('isOrganizer')->getData();
            $filters['isRegistered'] = $filterForm->get('isRegistered')->getData();
            $filters['isNotRegistered'] = $filterForm->get('isNotRegistered')->getData();
            $filters['isPast'] = $filterForm->get('isPast')->getData();

        }

        // Récupération des sorties filtrées
        $hangouts = $this->hangoutRepository->findFilteredEvent($user, $filters );

        dump($filters, $hangouts);

        return $this->render('hangouts/list.html.twig', [
            'hangouts' => $hangouts,
            'filterForm' => $filterForm,
            'filtersApplied' => $filterForm->isSubmitted(),
            ]);
    }

    #[Route('/add', name: 'add')]
    public function addHangout(): Response
    {
    }

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
    #[Route('/subscribe/{id}', name: 'subscribe', requirements: ['id'=>'\d+'])]
    public function subscribeToHangout(): Response
    {
    }

    #[Route('/unsubscribe/{id}', name: 'unsubscribe', requirements: ['id'=>'\d+'])]
    public function unsubscribeFromHangout(): Response
    {
    }
}
