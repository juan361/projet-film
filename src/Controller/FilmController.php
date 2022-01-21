<?php

namespace App\Controller;


use App\Entity\Film;
use App\Form\FilmType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\SearchFilm;


class FilmController extends AbstractController
{
    
    public function index(): Response
    {
        return $this->render('film/index.html.twig', [
            'controller_name' => 'FilmController',
        ]);
    }
    public function show(ManagerRegistry $doctrine):Response
    {
       $entitymanager = $doctrine->getManager();
       $film= $entitymanager->getRepository(Film::class)->findBy([],['note'=>'desc','nom'=>'asc']);
       return $this->render('film/listFilms.html.twig', [
           "films" => $film
       ]);
    }

    public function ajoutFilm(Request $request, ManagerRegistry $doctrine, SearchFilm $chercher):Response
    {
        $film = new Film();
        $film->setNbreVotants(1);
        $form= $this->createForm(FilmType::class, $film);
        $form ->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $film = $form->getData();
            
            $film->setDescription($chercher->search($film->getNom()));
            $entitymanager = $doctrine->getManager();
            $entitymanager->persist($film);

            $entitymanager->flush();
            return $this->redirect("/index");

        }

        return $this->renderForm('film/ajout.html.twig', ['form'=> $form]);
    }
    public function detailsFilm(){
        // a remplir
    }
}
