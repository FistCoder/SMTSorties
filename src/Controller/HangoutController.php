<?php

namespace App\Controller;

use App\Entity\Hangout;
use App\Entity\User;
use App\Form\FilterHangoutType;
use App\Form\HangoutType;
use App\Repository\HangoutRepository;
use App\Repository\StateRepository
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/hangouts', name: 'hangout_')]
final class HangoutController extends AbstractController
{

public function __construct(
        private readonly StateRepository $stateRepository,
        private readonly HangoutRepository $hangoutRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator)
    {
    }
    

    #[Route('/', name: 'list')]
    public function listHangouts(Request $request): Response

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

    

    

    #[Route('/detail/{id}', name: 'detail', requirements: ['id' => '\d+'])]
    public function detailHangout(int $id): Response
    {
        $hangout = $this->hangoutRepository->find($id);

        if (!$hangout) {
            throw $this->createNotFoundException("La sortie n'existe pas.");
        }

        return $this->render('hangout/detail.html.twig', [
            'hangout' => $hangout
        ]);
    }

    #[Route('/add', name: 'add')]
    public function addHangout(Request $request): Response
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();


        $hangout = new Hangout();
        $form = $this->createForm(HangoutType::class, $hangout);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            ;
            if ($form->get('save')->isClicked()) {
                $hangout->setState($this->stateRepository->findOneBy(['label' => 'CREATE']));
            } elseif ($form->get('publish')->isClicked()) {
                $hangout->setState($this->stateRepository->findOneBy(['label' => 'OPEN']));
            }
            $hangout->setCampus($user->getCampus());
            $hangout->setOrganizer($user);
            dump($hangout);
            $this->entityManager->persist($hangout);
            $this->entityManager->flush();
            $this->addFlash("success", "Sortie " . $hangout->getName() . "ajoutée");

            return $this->redirectToRoute('hangout_detail', ['id' => $hangout->getId()]);
        }

        return $this->render('hangout/add.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/modify/{id}', name: 'modify', requirements: ['id' => '\d+'])]
    public function modifyHangout(int $id): Response
    {
    }

    #[Route('/delete/{id}', name: 'delete', requirements: ['id' => '\d+'])]
    public function deleteHangout(int $id): Response
    {
    }
     
    #[Route('/cancel/{id}', name: 'cancel', requirements: ['id' => '\d+'])]
    public function cancelHangout(int $id): Response
    {
    }

    #[Route('/subscribe/{id}', name: 'subscribe', requirements: ['id' => '\d+'])]
    public function subscribeToHangout(int $id): Response
    {
        $hangout = $this->hangoutRepository->find($id);
        /**
         * @var User $user
         */
        $user = $this->getUser();

        if (!$hangout) {
            throw $this->createNotFoundException("La sortie n'existe pas.");
        }

        if ($hangout->getState()->getLabel() == "OPEN") {
        $hangout->addSubscriberLst($user);
        }

        if ($hangout->getSubscriberLst()->contains($user)) {
            $this->addFlash('error', $user->getFirstname(). " is already subscribed to this hangout. That's you");
            return $this->redirectToRoute('hangout_detail', ['id' => $hangout->getId()]);
        }


        $violations = $this->validator->validate($hangout);
        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $this->addFlash('danger', $violation->getMessage());
            }
        } else {
            $this->entityManager->persist($hangout);
            $this->entityManager->flush();
        }


        if ($hangout->getSubscriberLst()->count() == $hangout->getMaxParticipant()) {
            $hangout->setState($this->stateRepository->findOneBy(['label' => 'CLOSED']));
        }

        return $this->redirectToRoute('hangout_detail', ['id' => $hangout->getId()]);
    }

    #[Route('/unsubscribe/{id}', name: 'unsubscribe', requirements: ['id' => '\d+'])]
    public function unsubscribeFromHangout(): Response
    {
    }
}
