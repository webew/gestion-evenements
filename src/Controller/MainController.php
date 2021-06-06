<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Repository\EvenementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="main")
     */
    public function index(EvenementRepository $evenementRepository): Response
    {
        // récupération de la connection à la bdd
        $conn = $this->getDoctrine()->getConnection();

        // récupération du user connecté
        $user = $this->getUser();
        dump($user);

        // la requête sql
        if ($this->getUser()) { // si un user est connecté
            $sql = "SELECT evenement.id,titre,organisateur.nom,date,eu.user_id,lieu,description,image_src,(nb_places-count(eu1.evenement_id)) as nbRestantes
                    FROM `evenement` 
                    LEFT JOIN (SELECT * FROM evenement_user WHERE evenement_user.user_id= :userId) eu ON evenement.id=eu.evenement_id
                    LEFT JOIN evenement_user eu1 ON evenement.id=eu1.evenement_id
                    LEFT JOIN organisateur ON evenement.organisateur_id=organisateur.id
                    GROUP by evenement.id ORDER BY date ASC";

            // envoi de la requête
            $stmt = $conn->prepare($sql);
            $stmt->execute([":userId" => $user->getId()]);
            $evenements = $stmt->fetchAll();
        } else { // s'il n'y a pas de user connecté
            $sql = "SELECT evenement.id,titre,organisateur.nom,date,lieu,description,image_src,(nb_places-count(eu1.evenement_id)) as nbRestantes
                    FROM `evenement` 
                    LEFT JOIN evenement_user eu1 ON evenement.id=eu1.evenement_id
                    LEFT JOIN organisateur ON evenement.organisateur_id=organisateur.id
                    GROUP by evenement.id ORDER BY date ASC";
            // envoi de la requête
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $evenements = $stmt->fetchAll();
        }
        dump($evenements);

        // $evenements = $evenementRepository->findAll();

        return $this->render('main/index.html.twig', [
            "evenements" => $evenements
        ]);
    }

    /**
     * @Route("/minscrire/{id}", name="minscrire")
     */
    public function minscrire(Evenement $evenement)
    {
        $evenementId = $evenement->getId();
        $userId = $this->getUser()->getId();
        $sql = "INSERT INTO evenement_user (evenement_id, user_id) VALUES (:evenement_id, :user_id)";
        $conn = $this->getDoctrine()->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute([":evenement_id" => $evenementId, ":user_id" => $userId]);
        return $this->redirectToRoute("main");
    }

    /**
     * @Route("/medesinscrire/{id}", name="medesinscrire")
     */
    public function medesinscrire(Evenement $evenement)
    {
        $evenementId = $evenement->getId();
        $userId = $this->getUser()->getId();
        $sql = "DELETE FROM evenement_user WHERE evenement_id= :evenement_id AND user_id= :user_id";
        $conn = $this->getDoctrine()->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute([":evenement_id" => $evenementId, ":user_id" => $userId]);
        return $this->redirectToRoute("main");
    }
}
