<?php

namespace App\Controller;

use App\Entity\Film;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FilmController extends AbstractController
{
    #[Route('/film', name: 'film')]
    public function index(): Response
    {
        return $this->render('film/index.html.twig', [
            'controller_name' => 'FilmController',
        ]);
    }
    public function show(ManagerRegistry $doctrine): Response
    {
       $entitymanager = $doctrine->getManager();
       $film= $entitymanager->getRepository(Film::class)->findBy([],['Ònote'=>'desc','nom'=>'asc']);
       return $this->render('listFilms.html.twig', [
           "films" => $film
       ]);
    }
}
