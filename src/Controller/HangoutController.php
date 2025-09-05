<?php

namespace App\Controller;

use App\Entity\Hangout;
use App\Entity\User;
use App\Form\FilterHangoutType;
use App\Form\HangoutType;
use App\Form\Models\FiltresModel;
use App\Repository\HangoutRepository;
use App\Repository\StateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use function PHPUnit\Framework\throwException;

#[Route('/hangouts', name: 'hangout_')]
final class HangoutController extends AbstractController
{

    public function __construct(
        private readonly StateRepository        $stateRepository,
        private readonly HangoutRepository      $hangoutRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface     $validator)
    {
    }


    #[Route('/', name: 'list')]
    public function listHangouts(Request $request): Response
    {

        /**
         * @var User $user
         */
        $user = $this->getUser();

        $filtersModel = new FiltresModel();//permet de mapper les données directement atravers le model

        if (!$user) {
            // Gère le cas utilisateur non connecté (redirige, exception, etc.)
            throw $this->createAccessDeniedException('Vous devez être connecté');
        }

//creation du form - et je lui passe le model
        $filterForm = $this->createForm(FilterHangoutType::class, $filtersModel);
        $filterForm->handleRequest($request);


//recuperation des donées du formulaire de filtres remplis et ajout de ces données dans le tableau de filtre qui seras envoyer au repository
        $hangouts = [];

        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $filters = $filterForm->getData();

            $hangouts = $this->hangoutRepository->findFilteredEvent($user, $filters);
        } else {
            // Par défaut (pas de filtre), recupère tout ou selon ta logique
            $hangouts = $this->hangoutRepository->findFilteredEvent($user, new FiltresModel());
        }
//        dump($filters, $hangouts);

        return $this->render('hangout/list.html.twig', [
            'hangouts' => $hangouts,
            'filterForm' => $filterForm,
            'filtersApplied' => $filterForm->isSubmitted(),
        ]);
    }


    #[
        Route('/detail/{id}', name: 'detail', requirements: ['id' => '\d+'])]
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

    #[IsGranted('POST_EDIT', 'hangout')]
    #[Route('/modify/{id}', name: 'modify', requirements: ['id' => '\d+'])]
    public function modifyHangout(Request $request, Hangout $hangout): Response
    {

        $form = $this->createForm(HangoutType::class, $hangout);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('delete')->isClicked()) {
                $this->entityManager->remove($hangout);
                $this->entityManager->flush();
                $this->addFlash('success', 'Sortie supprimée avec success');
                return $this->redirectToRoute('hangout_list', ['id' => $hangout->getId()]);
            } elseif ($form->get('save')->isClicked()) {
                $this->entityManager->persist($hangout);
                $this->entityManager->flush();

                $this->addFlash("success", "Sortie mise a jours !");
                return $this->redirectToRoute('hangout_detail', ['id' => $hangout->getId()]);
            }

        }
        return $this->render('hangout/modify.html.twig', [
            'formUpdate' => $form,
            'hangout' => $hangout,
        ]);
    }

//    #[IsGranted('POST_DELETE', 'hangout')]//c'est les acces grace au voter ca marche pour le bouton de edition
//    #[Route('/delete/{id}', name: 'delete', requirements: ['id' => '\d+'])]
//    public function deleteHangout(int $id): Response
//    {
//        $hangout = $this->hangoutRepository->find($id);
//        if (!$hangout) {
//            throw $this->createNotFoundException("La sortie n'existe pas.");
//        }
//
//        $this->entityManager->remove($hangout);
//        $this->entityManager->flush();
//
//        $this->addFlash('sucess', 'Votre Sortie a bien été suprimmée');
//        return $this->redirectToRoute('hangout_list');
//    }

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
