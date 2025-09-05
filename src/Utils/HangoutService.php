<?php

namespace App\Utils;

use App\Repository\HangoutRepository;
use App\Repository\StateRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HangoutService
{

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {

    }

    public function updateState(
        HangoutRepository $hangoutRepository,
        StateRepository $stateRepository,
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response
    {
        dump("test state update");
        $dateNow = new DateTimeImmutable();
        $hangoutLst = $hangoutRepository->findAll();
        foreach ($hangoutLst as $hangout) {
            $dateEnd = clone $hangout->getStartingDateTime();

            $hours = (int) $hangout->getLength()->format('H');
            $minutes = (int) $hangout->getLength()->format('i');
            $totalMinutes = $hours * 60 + $minutes;

            dump($totalMinutes);

            if ($hangout->getState()=== $stateRepository->findOneBy(['label' => 'CANCELLED'])) {

                if ($dateEnd->modify('+ 1 month') > $dateNow) {
                    $hangout->setState($stateRepository->findOneBy(['label' => 'ARCHIVED']));
                    dump($hangout);

                }

            } else {

                if ($hangout->getLastSubmitDate() > $dateNow or $hangout->getSubscriberLst()->count() >= $hangout->getMaxParticipant()) {
                    $state = $stateRepository->findOneBy(['label' => 'CLOSED']);
                    $hangout->setState($state);
                    dump($hangout);
                }
                if ($hangout->getStartingDateTime() > $dateNow) {
                    $hangout->setState($stateRepository->findOneBy(['label' => 'IN_PROCESS']));
                    dump($hangout);
                }
                if ($dateEnd->modify('+' .$totalMinutes. 'minutes')> $dateNow) {
                    $hangout->setState($stateRepository->findOneBy(['label' => 'FINISHED']));
                    dump($hangout);
                }
                if ($dateEnd->modify('+' .$totalMinutes. 'minutes + 1 month') > $dateNow) {
                    $hangout->setState($stateRepository->findOneBy(['label' => 'ARCHIVED']));
                    dump($hangout);
                }
            }
            $this->entityManager->persist($hangout);
            $this->entityManager->flush();
        }


    }


}