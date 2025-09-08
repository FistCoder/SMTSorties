<?php

namespace App\Controller;

use App\Entity\Hangout;
use App\Entity\User;
use App\Form\FilterHangoutType;
use App\Form\HangoutType;
use App\Repository\HangoutRepository;
use App\Repository\StateRepository;
use App\Repository\UserRepository;
use App\Utils\HangoutService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;



#[Route('/hangouts', name: 'hangout_')]
final class HangoutController extends AbstractController
{

    public function __construct(
        private readonly StateRepository        $stateRepository,
        private readonly HangoutRepository      $hangoutRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface     $validator,
        private readonly HangoutService $hangoutService
    )
    {
    }


    #[Route('/', name: 'list')]
    public function listHangouts(Request $request, HangoutService $hangoutService): Response
    {

        /**
         * @var User $user
         */
        $user = $this->getUser();

        if (!$user) {
            // Gère le cas utilisateur non connecté (redirige, exception, etc.)
            throw $this->createAccessDeniedException('Vous devez être connecté');
        }



        $majHangoutState = $hangoutService->updateState($this->hangoutRepository,  $this->stateRepository);
        dump($majHangoutState);

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
        $hangouts = $this->hangoutRepository->findFilteredEvent($user, $filters);

        return $this->render('hangout/list.html.twig', [
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

        $request->query->get('cancelMotif', 'not_existing');

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
    public function cancelHangout(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        HangoutRepository $hangoutRepository,
        StateRepository $stateRepository
    ): Response
    {
        $hangout = $hangoutRepository->find($id);
        $state = $stateRepository->findOneBy(['label' => 'CANCELLED']);
        $dateNow = new DateTimeImmutable();
        dump($dateNow);

        if (!$hangout) {
            throw $this->createNotFoundException("Hangout not found");
        }
        if($request->isMethod('POST')) {

            if ($hangout->getStartingDateTime() < $dateNow) {
                $this->addFlash('', "la sortie " . $hangout->getName() . " a déjà commencé, elle ne peut pas être annulée");
                return $this->redirectToRoute('hangout_detail', ['id' => $hangout->getId()]);
            } else {
            $cancelMotif = $request->request->get('cancelMotif', null);
            $hangoutDetail = $hangout->getDetail();
            $hangout->setDetail($hangoutDetail . '. Annulé : ' . $cancelMotif);
            $hangout->setState($state);
            $this->entityManager->persist($hangout);
            $this->entityManager->flush();
            $this->addFlash('success', "Sortie " . $hangout->getName() . " cancelled");

            return $this->redirectToRoute('hangout_detail', ['id' => $hangout->getId()]);
            }
        }

        return $this->render('hangout/cancel.html.twig', [
            'hangout'=> $hangout
        ]);

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

        if ($hangout->getSubscriberLst()->contains($user)) {
            $this->addFlash('danger', $user->getFirstname() . " is already subscribed to this hangout. That's you");
            return $this->redirectToRoute('hangout_detail', ['id' => $hangout->getId()]);
        }
        if ($hangout->getState()->getLabel() == "OPEN") {
            $hangout->addSubscriberLst($user);
        }

        if ($hangout->getSubscriberLst()->count() == $hangout->getMaxParticipant()) {
            $hangout->setState($this->stateRepository->findOneBy(['label' => 'CLOSED']));
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

        return $this->redirectToRoute('hangout_detail', ['id' => $hangout->getId()]);
    }

    #[Route('/unsubscribe/{id}', name: 'unsubscribe', requirements: ['id' => '\d+'])]
    public function unsubscribeFromHangout(int $id, Request $request): Response
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $hangout = $this->hangoutRepository->find($id);
        if (!$hangout) {
            throw $this->createNotFoundException("La sortie n'existe pas.");
        }

        if ($hangout->getSubscriberLst()->contains($user)) {
            $hangout->removeSubscriberLst($user);
        }

        if ($hangout->getSubscriberLst()->count() != $hangout->getMaxParticipant()) {
            $hangout->setState($this->stateRepository->findOneBy(['label' => 'OPEN']));
        }

        $violations = $this->validator->validate($hangout);
        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $this->addFlash('danger', $violation->getMessage());
            }
        } else {
            $this->addFlash('success', "Désistement avec success.");
            $this->entityManager->persist($hangout);
            $this->entityManager->flush();
        }

        $referer = $request->headers->get('referer');

        // Validate referer: must be a proper URL and same host
        if ($referer && filter_var($referer, FILTER_VALIDATE_URL)) {
            return new RedirectResponse($referer);
        }

        // I'd rather use an event listener that saves the last page in session,
        // but im pretty sure there is a better solution,
        // so it's going to stay like this for a while.
        // Right now it relies on the $referer, which may or may not exist
        return $this->redirectToRoute('hangout_list');

    }

}
