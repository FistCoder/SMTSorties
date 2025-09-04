<?php

namespace App\Security\Voter;

use App\Entity\Hangout;
use phpDocumentor\Reflection\DocBlock\Tags\Throws;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use function PHPUnit\Framework\throwException;

final class HangoutVoter extends Voter
{
    public const EDIT = 'POST_EDIT';

//    public const VIEW = 'POST_VIEW';

    public function __construct(private Security $security)
    {

    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::EDIT])
            && $subject instanceof \App\Entity\Hangout;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        /**
        * @var Hangout $subject
         */

        if (self::EDIT) {
            return ($user === $subject->getOrganizer() && $this->security->isGranted('ROLE_USER'));
        }

        // ... (check conditions and return true to grant permission) ...
//        switch ($attribute) {
//            case self::EDIT:
//                // logic to determine if the user can EDIT
//                // return true or false
//                break;
//
//            case self::VIEW:
//                // logic to determine if the user can VIEW
//                // return true or false
//                break;
//        }
        else {
            return throw new \LogicException('This code should not be reached!');
        }
    }
}
