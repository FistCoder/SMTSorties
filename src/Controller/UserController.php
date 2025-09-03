<?php

namespace App\Controller;

use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/user', name: 'user_')]
final class UserController extends AbstractController
{

    public function __construct(private UserPasswordHasherInterface $userPasswordHasher)
    {
    }
    #[Route('/detail/{id}', name: 'detail')]
    public function userDetail(int $id): Response
    {
    }

    #[Route('/modify', name: 'modify')]
    public function userModify(
        Request $request,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository
    ): Response
    {
        $tempUser = $this->getUser();

        $user = $userRepository->findOneBy(['email' => $tempUser->getUserIdentifier()]);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }
        $userForm = $this->createForm(UserType::class, $user);
        $userForm->handleRequest($request);

        if ($userForm->isSubmitted() && $userForm->isValid()) {

//            $existingUser = $userRepository->findOneBy(['username' => $user->getUsername()]);

//            if ($existingUser && $existingUser->getId() !== $user->getId()){
//                $userForm->get('username')->addError(new FormError('Username already taken'));
//            } else {
            if($userForm->get('confirmPassword')->getData()){
                    $user->setPassword($this->userPasswordHasher->hashPassword($user, $userForm->get('confirmPassword')->getData()));
            }
                    $entityManager->persist($user);
                    $entityManager->flush();
                    $this->addFlash("success", "User modified successfully");
                    return $this->redirectToRoute('user_modify', ['id' => $user->getId()]);
//        }
    }
        return $this->render('user/modify.html.twig', [
            'userForm' => $userForm,
        ]);
        }
}
