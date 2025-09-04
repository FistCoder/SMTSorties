<?php

namespace App\Controller;

use App\Entity\Hangout;
use App\Entity\User;
use App\Form\HangoutType;
use App\Repository\HangoutRepository;
use App\Repository\StateRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
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
    public function listHangouts(): Response
    {
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
    public function addHangout(Request $request, StateRepository $stateRepository): Response
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
                $hangout->setState($stateRepository->findOneBy(['label' => 'CREATE']));
            } elseif ($form->get('publish')->isClicked()) {
                $hangout->setState($stateRepository->findOneBy(['label' => 'OPEN']));
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
    public function subscribeToHangout(): Response
    {
    }

    #[Route('/unsubscribe/{id}', name: 'unsubscribe', requirements: ['id' => '\d+'])]
    public function unsubscribeFromHangout(): Response
    {
    }

    #[Route('/', name: 'update_list')]
    public function updateState(
        HangoutRepository $hangoutRepository,
        StateRepository $stateRepository,

        Request $request,
        EntityManagerInterface $entityManager,
    ): Response
    {
        $dateNow = new DateTimeImmutable();
        $hangoutLst = $hangoutRepository->findAll();
        foreach ($hangoutLst as $hangout) {
            $dateEnd = clone $hangout->getStartingDateTime();
            $lengthSeconds =  $hangout->getLength()->days * 24 * 60 * 60 +
                        $hangout->getLength()->h * 60 * 60 +
                        $hangout->getLength()->i * 60 +
                        $hangout->getLength()->s;
            ;
            if ($hangout->getState()=== $stateRepository->findOneBy(['label' => 'CANCELLED'])) {

                if ($dateEnd->modify('+ 1 month') > $dateNow) {
                    $hangout->setState($stateRepository->findOneBy(['label' => 'ARCHIVED']));
                }

            } else {

                if ($hangout->getLastSubmitDate() > $dateNow or $hangout->getSubscriberLst()->count() >= $hangout->getMaxParticipant()) {
                    $state = $stateRepository->findOneBy(['label' => 'CLOSED']);
                    $hangout->setState($state);
                }
                if ($hangout->getStartingDateTime() > $dateNow) {
                    $hangout->setState($stateRepository->findOneBy(['label' => 'IN_PROCESS']));
                }
                if ($dateEnd->modify('+' .$lengthSeconds. 'seconds')> $dateNow) {
                    $hangout->setState($stateRepository->findOneBy(['label' => 'FINISHED']));
                }
                if ($dateEnd->modify('+' .$lengthSeconds. 'seconds + 1 month' > $dateNow) {
                    $hangout->setState($stateRepository->findOneBy(['label' => 'ARCHIVED']));
                }
            }
        }

    }

}
