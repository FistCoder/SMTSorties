<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin', name: 'admin_')]
final class AdminController extends AbstractController

{
    #[Route('/', name: 'dashboard')]
    public function dashboard(): Response
    {
        return $this->render('/dashboard.html.twig', [

        ]);
    }

    #[Route('/cities', name: 'cities_List')]
    public function citiesLst(): Response
    {
        return $this->render('/cities.html.twig', [

        ]);
    }

    #[Route('/cities/add', name: 'cities_add')]
    public function addCity(): Response
    {
        return $this->render('/add.html.twig', [

        ]);
    }

    #[Route('/cities/delete/{id}', name: 'cities_delete', requirements: ['id'=>'\d+'])]
    public function deleteCity(int $id): Response
    {
        return $this->render('/delete.html.twig', []);
    }

    #[Route('/cities/modify/{id}', name: 'cities_modify', requirements: ['id'=>'\d+'])]
    public function modifyCities(int $id): Response
    {
        return $this->render('/modifyCities.html.twig', []);
    }


    #[Route('/campus', name: 'campus_List')]
    public function campusLst(): Response
    {
        return $this->render('/campus.html.twig', [

        ]);
    }

    #[Route('/campus/add', name: 'campus_add')]
    public function addCampus(): Response
    {
        return $this->render('/addCampus.html.twig', [

        ]);
    }

    #[Route('/campus/delete/{id}', name: 'campus_delete', requirements: ['id'=>'\d+'])]
    public function deleteCampus(int $id): Response
    {

    }

    #[Route('/campus/modify/{id}', name: 'campus_modify', requirements: ['id'=>'\d+'])]
    public function modifyCampus(int $id): Response
    {
        return $this->render('/modifyCampus.html.twig', []);
    }



    #[Route('/users', name: 'users_List')]
    public function usersLst(): Response
    {
        return $this->render('admin/users/list.html.twig', [

        ]);
    }

    #[Route('/users/add', name: 'users_add')]
    public function addUsers(): Response
    {
        return $this->render('/addCampus.html.twig', [

        ]);
    }

    #[Route('/users/delete/{id}', name: 'users_delete', requirements: ['id'=>'\d+'])]
    public function deleteUsers(int $id): Response
    {

    }

    #[Route('/users/modify/{id}', name: 'users_modify', requirements: ['id'=>'\d+'])]
    public function modifyUsers(int $id): Response
    {
        return $this->render('/modifyUsers.html.twig', []);
    }

}
