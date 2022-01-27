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
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Form\ImportCsvForm;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class FilmController extends AbstractController
{
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


    public function details(ManagerRegistry $doctrine, int $id):Response
    {
       $entitymanager = $doctrine->getManager();
       $film= $entitymanager->getRepository(Film::class)->find($id);
       return $this->render('film/details.html.twig', ["film" => $film]);
    }


    public function delete(ManagerRegistry $doctrine, Film $film, Request $request){
        $form = $this->createFormBuilder( [])
        ->add('mdp', PasswordType::class)
        ->add("submit", SubmitType::class)
        ->getForm();
    
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $data =$form->getData();
            if($data['mdp']==$this->getParameter('mdp')){
                $em = $doctrine->getManager();
                $em->remove($film);
                $em->flush();
                return $this->redirect("/index");
            }
        }
        return $this->renderForm('film/delete.html.twig',['form'=> $form]);
    }


    public function importCsv(Request $request, ManagerRegistry $doctrine):Response{
        $serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);

        $form = $this->createFormBuilder( []) 
        ->add('importCsv', FileType::class, [
            'label' => 'import (CSV file)',
            // unmapped means that this field is not associated to any entity property
            'mapped' => false,
            // make it optional so you don't have to re-upload the CSV file
            // every time you edit the Product details
            'required' => true,
            // unmapped fields can't define their validation using annotations
            // in the associated entity, so you can use the PHP constraint classes
            'constraints' => [
                new File([
                    'maxSize' => '1024k',
                    'mimeTypes' => [
                        'application/csv'
                    ]
                ])
            ],
        ])
        ->add("submit", SubmitType::class)
        ->getForm();
        $form -> handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $csvFile = $form->get("importCsv")->getData();
            // decoding CSV contents
            $data = $serializer->decode(file_get_contents($csvFile->getPathname()), 'csv');
            foreach($data as $movie){
                $film = new Film();
                $film->setNom($movie['nom']);
                $film->setNbreVotants(1);
                $film->setNote($movie['note']);
                $film->setDescription($movie['description']);
                $entitymanager = $doctrine->getManager();
                $entitymanager->persist($film);
                $entitymanager->flush(); 

            }
            return $this->redirect("/index");
        }

        return $this->renderForm('film/importCsv.html.twig',['form'=> $form]);
    }
}

