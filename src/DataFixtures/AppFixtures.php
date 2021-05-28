<?php

namespace App\DataFixtures;

use App\Entity\Evenement;
use App\Entity\Organisateur;
use App\Entity\User;
use DateTime;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private UserPasswordEncoderInterface $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        // Création d'utilisateurs
        $toto = new User();
        $toto->setEmail("toto@toto.fr");
        $toto->setNom("TOTO");
        $toto->setPrenom("Toto");
        $hash = $this->encoder->encodePassword($toto, "toto");
        $toto->setPassword($hash);
        $manager->persist($toto); // prépare la requête

        // Les organisateurs

        $fab = new Organisateur();
        $fab->setNom("Fabrique Numérique");
        $manager->persist($fab);

        // Les événements

        // couscous
        $couscous = new Evenement();
        $couscous->setDate(new DateTime("2021-6-18"));
        $couscous->setTitre("Couscous Party");
        $couscous->setLieu("Helioparc Bâtiment Monge");
        $couscous->setDescription("Une couscous party avec tout le monde");
        $couscous->setImageSrc("images/couscous.png");
        $couscous->setOrganisateur($fab);
        $couscous->setNbPlaces(30);

        // apero
        $apero = new Evenement();
        $apero->setDate(new DateTime("2021-7-15"));
        $apero->setTitre("Un apéro chez Mohammed");
        $apero->setLieu("Chez Mohammed");
        $apero->setDescription("Un apéro chez Mohammed avec tout le monde");
        $apero->setImageSrc("images/couscous.png");
        $apero->setOrganisateur($fab);
        $apero->setNbPlaces(350);
        $manager->persist($apero);

        // Les inscriptions

        $couscous->addUser($toto);
        $manager->persist($couscous);

        $manager->flush(); // envoie la requête => transaction avec la bdd
    }
}
