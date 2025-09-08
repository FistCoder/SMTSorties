<?php

namespace App\Utils;

use App\Entity\Hangout;
use App\Repository\HangoutRepository;
use App\Repository\StateRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HangoutService
{

    public function __construct(private EntityManagerInterface $entityManager)
    {

    }

    public function updateState(

        HangoutRepository $hangoutRepository,
        StateRepository $stateRepository,
        //Request $request,
        //EntityManagerInterface $entityManager,
    ): void
    {
        dump("test state update");
        $dateNow = new DateTimeImmutable();
        $hangoutList = $hangoutRepository->findAll();
        $stateList = $stateRepository->findAll();
        dump($stateList);

        foreach ($stateList as $state) {
            $states[$state->getLabel()] = $state;
        }
        dump($states);
        //$states['CLOSED'] =


        foreach ($hangoutList as $hangout) {
            $dateEnd = clone $hangout->getStartingDateTime();

            $hours = (int) $hangout->getLength()->format('H');
            $minutes = (int) $hangout->getLength()->format('i');
            $totalMinutes = $hours * 60 + $minutes;

            dump($totalMinutes);

            if ($hangout->getState()->getLabel()=== 'CANCELLED'){

                if ($dateEnd->modify('+ 1 month') < $dateNow) {
                    $hangout->setState($states['ARCHIVED']);
                    dump($dateEnd);
                }

            } else {

                if ($hangout->getLastSubmitDate() < $dateNow or $hangout->getSubscriberLst()->count() >= $hangout->getMaxParticipant()) {
                    $hangout->setState($states['CLOSED']);
                }
                if ($hangout->getStartingDateTime() < $dateNow) {
                    $hangout->setState($states['IN_PROCESS']);
                }
                if ($dateEnd->modify('+' .$totalMinutes. 'minutes')< $dateNow) {
                    $hangout->setState($states['FINISHED']);
                    dump($dateEnd);
                }
                if ($dateEnd->modify('+' .$totalMinutes. 'minutes + 1 month') < $dateNow) {
                    $hangout->setState($states['ARCHIVED']);
                    dump($dateEnd);
                }
            }
            $this->entityManager->persist($hangout);

        }
        $this->entityManager->flush();

    }


}