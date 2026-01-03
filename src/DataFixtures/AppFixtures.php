<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Fournisseur;
use App\Entity\Intervention;
use App\Entity\InterventionLog;
use App\Entity\Machine;
use App\Entity\MouvementStock;
use App\Entity\Panne;
use App\Entity\Piece;
use App\Entity\PieceIntervention;
use App\Entity\Technicien;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        // =============================================
        // USERS
        // =============================================

        // Admin
        $admin = new User();
        $admin->setEmail('admin@local.host');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setNom('Alami');
        $admin->setPrenom('Mohammed');
        $admin->setPassword($this->hasher->hashPassword($admin, 'password'));
        $manager->persist($admin);

        // Receptionist
        $receptionist = new User();
        $receptionist->setEmail('reception@local.host');
        $receptionist->setRoles(['ROLE_RECEPTIONIST']);
        $receptionist->setNom('Bennani');
        $receptionist->setPrenom('Fatima');
        $receptionist->setPassword($this->hasher->hashPassword($receptionist, 'password'));
        $manager->persist($receptionist);

        // Techniciens Users
        $userTech1 = new User();
        $userTech1->setEmail('tech1@local.host');
        $userTech1->setRoles(['ROLE_TECHNICIEN']);
        $userTech1->setNom('El Idrissi');
        $userTech1->setPrenom('Youssef');
        $userTech1->setPassword($this->hasher->hashPassword($userTech1, 'password'));
        $manager->persist($userTech1);

        $userTech2 = new User();
        $userTech2->setEmail('tech2@local.host');
        $userTech2->setRoles(['ROLE_TECHNICIEN']);
        $userTech2->setNom('Chraibi');
        $userTech2->setPrenom('Omar');
        $userTech2->setPassword($this->hasher->hashPassword($userTech2, 'password'));
        $manager->persist($userTech2);

        $userTech3 = new User();
        $userTech3->setEmail('tech3@local.host');
        $userTech3->setRoles(['ROLE_TECHNICIEN']);
        $userTech3->setNom('Tazi');
        $userTech3->setPrenom('Karim');
        $userTech3->setPassword($this->hasher->hashPassword($userTech3, 'password'));
        $manager->persist($userTech3);

        // =============================================
        // TECHNICIENS
        // =============================================

        $tech1 = new Technicien();
        $tech1->setUser($userTech1);
        $tech1->setSpecialite('Électrique');
        $tech1->setTauxHoraire(50.0);
        $tech1->setStatut('Disponible');
        $manager->persist($tech1);

        $tech2 = new Technicien();
        $tech2->setUser($userTech2);
        $tech2->setSpecialite('Mécanique');
        $tech2->setTauxHoraire(55.0);
        $tech2->setStatut('Disponible');
        $manager->persist($tech2);

        $tech3 = new Technicien();
        $tech3->setUser($userTech3);
        $tech3->setSpecialite('Hydraulique');
        $tech3->setTauxHoraire(60.0);
        $tech3->setStatut('En intervention');
        $manager->persist($tech3);

        // =============================================
        // CLIENTS
        // =============================================

        $client1 = new Client();
        $client1->setNom('SARL TechnoPlus');
        $client1->setTelephone('0522-123456');
        $client1->setEmail('contact@technoplus.ma');
        $client1->setAdresse('Zone Industrielle Ain Sebaa, Casablanca');
        $client1->setIce('001234567000089');
        $client1->setRc('123456');
        $client1->setPatente('12345678');
        $manager->persist($client1);

        $client2 = new Client();
        $client2->setNom('Atlas Industries');
        $client2->setTelephone('0537-654321');
        $client2->setEmail('info@atlas-industries.ma');
        $client2->setAdresse('Technopolis, Rabat');
        $client2->setIce('001234567000090');
        $client2->setRc('654321');
        $client2->setPatente('87654321');
        $manager->persist($client2);

        $client3 = new Client();
        $client3->setNom('Maroc Mécanique SARL');
        $client3->setTelephone('0528-111222');
        $client3->setEmail('contact@marocmeca.ma');
        $client3->setAdresse('Zone Franche, Tanger');
        $client3->setIce('001234567000091');
        $client3->setRc('789012');
        $client3->setPatente('11223344');
        $manager->persist($client3);

        $client4 = new Client();
        $client4->setNom('Agro-Sud SA');
        $client4->setTelephone('0524-333444');
        $client4->setEmail('direction@agrosud.ma');
        $client4->setAdresse('Route de Safi, Marrakech');
        $client4->setIce('001234567000092');
        $client4->setRc('345678');
        $client4->setPatente('55667788');
        $manager->persist($client4);

        $client5 = new Client();
        $client5->setNom('Textile Nord');
        $client5->setTelephone('0539-555666');
        $client5->setEmail('info@textilenord.ma');
        $client5->setAdresse('Boulevard Moulay Ismail, Fès');
        $manager->persist($client5);

        // =============================================
        // FOURNISSEURS
        // =============================================

        $fournisseur1 = new Fournisseur();
        $fournisseur1->setNom('ElectroParts Maroc');
        $fournisseur1->setEmail('commandes@electroparts.ma');
        $fournisseur1->setTelephone('0522-987654');
        $fournisseur1->setAdresse('Derb Omar, Casablanca');
        $fournisseur1->setDelaiLivraison(3);
        $manager->persist($fournisseur1);

        $fournisseur2 = new Fournisseur();
        $fournisseur2->setNom('Meca Distribution');
        $fournisseur2->setEmail('ventes@mecadist.ma');
        $fournisseur2->setTelephone('0537-456789');
        $fournisseur2->setAdresse('Hay Riad, Rabat');
        $fournisseur2->setDelaiLivraison(5);
        $manager->persist($fournisseur2);

        $fournisseur3 = new Fournisseur();
        $fournisseur3->setNom('Hydro-Tech International');
        $fournisseur3->setEmail('sales@hydrotech.com');
        $fournisseur3->setTelephone('+34-612345678');
        $fournisseur3->setAdresse('Barcelona, Espagne');
        $fournisseur3->setDelaiLivraison(15);
        $manager->persist($fournisseur3);

        $fournisseur4 = new Fournisseur();
        $fournisseur4->setNom('Roulements Express');
        $fournisseur4->setEmail('contact@roulements-express.ma');
        $fournisseur4->setTelephone('0522-111333');
        $fournisseur4->setAdresse('Zone Industrielle Moulay Rachid, Casablanca');
        $fournisseur4->setDelaiLivraison(2);
        $manager->persist($fournisseur4);

        // =============================================
        // PIECES (Spare Parts)
        // =============================================

        $piece1 = new Piece();
        $piece1->setReference('EL-MOT-001');
        $piece1->setNom('Moteur électrique 5kW');
        $piece1->setDescription('Moteur asynchrone triphasé 5kW, 1500 tr/min');
        $piece1->setPrixUnitaire(2500.00);
        $piece1->setQuantiteStock(8);
        $piece1->setSeuilAlerte(3);
        $piece1->setEmplacement('Étagère A1');
        $piece1->setFournisseur($fournisseur1);
        $manager->persist($piece1);

        $piece2 = new Piece();
        $piece2->setReference('EL-VAR-002');
        $piece2->setNom('Variateur de fréquence 7.5kW');
        $piece2->setDescription('Variateur ABB ACS310, entrée 400V triphasé');
        $piece2->setPrixUnitaire(3200.00);
        $piece2->setQuantiteStock(4);
        $piece2->setSeuilAlerte(2);
        $piece2->setEmplacement('Étagère A2');
        $piece2->setFournisseur($fournisseur1);
        $manager->persist($piece2);

        $piece3 = new Piece();
        $piece3->setReference('ME-ROU-001');
        $piece3->setNom('Roulement à billes 6205');
        $piece3->setDescription('Roulement SKF 6205-2RS, étanche');
        $piece3->setPrixUnitaire(85.00);
        $piece3->setQuantiteStock(50);
        $piece3->setSeuilAlerte(15);
        $piece3->setEmplacement('Tiroir B3');
        $piece3->setFournisseur($fournisseur4);
        $manager->persist($piece3);

        $piece4 = new Piece();
        $piece4->setReference('ME-ROU-002');
        $piece4->setNom('Roulement à rouleaux coniques');
        $piece4->setDescription('Roulement 32210, charge radiale et axiale');
        $piece4->setPrixUnitaire(145.00);
        $piece4->setQuantiteStock(25);
        $piece4->setSeuilAlerte(8);
        $piece4->setEmplacement('Tiroir B4');
        $piece4->setFournisseur($fournisseur4);
        $manager->persist($piece4);

        $piece5 = new Piece();
        $piece5->setReference('HY-POM-001');
        $piece5->setNom('Pompe hydraulique à engrenages');
        $piece5->setDescription('Pompe Bosch Rexroth, 20 l/min, 250 bar');
        $piece5->setPrixUnitaire(4500.00);
        $piece5->setQuantiteStock(3);
        $piece5->setSeuilAlerte(2);
        $piece5->setEmplacement('Étagère C1');
        $piece5->setFournisseur($fournisseur3);
        $manager->persist($piece5);

        $piece6 = new Piece();
        $piece6->setReference('HY-FIL-001');
        $piece6->setNom('Filtre hydraulique');
        $piece6->setDescription('Élément filtrant 10 microns, retour');
        $piece6->setPrixUnitaire(120.00);
        $piece6->setQuantiteStock(20);
        $piece6->setSeuilAlerte(5);
        $piece6->setEmplacement('Étagère C2');
        $piece6->setFournisseur($fournisseur3);
        $manager->persist($piece6);

        $piece7 = new Piece();
        $piece7->setReference('ME-COU-001');
        $piece7->setNom('Courroie trapézoïdale A68');
        $piece7->setDescription('Courroie de transmission, section A, longueur 68"');
        $piece7->setPrixUnitaire(45.00);
        $piece7->setQuantiteStock(30);
        $piece7->setSeuilAlerte(10);
        $piece7->setEmplacement('Tiroir D1');
        $piece7->setFournisseur($fournisseur2);
        $manager->persist($piece7);

        $piece8 = new Piece();
        $piece8->setReference('EL-CAP-001');
        $piece8->setNom('Capteur de température PT100');
        $piece8->setDescription('Sonde PT100, plage -50°C à +200°C');
        $piece8->setPrixUnitaire(180.00);
        $piece8->setQuantiteStock(12);
        $piece8->setSeuilAlerte(4);
        $piece8->setEmplacement('Tiroir E2');
        $piece8->setFournisseur($fournisseur1);
        $manager->persist($piece8);

        $piece9 = new Piece();
        $piece9->setReference('EL-REL-001');
        $piece9->setNom('Relais thermique 10-16A');
        $piece9->setDescription('Relais de protection moteur, réglable 10-16A');
        $piece9->setPrixUnitaire(95.00);
        $piece9->setQuantiteStock(18);
        $piece9->setSeuilAlerte(5);
        $piece9->setEmplacement('Tiroir E3');
        $piece9->setFournisseur($fournisseur1);
        $manager->persist($piece9);

        $piece10 = new Piece();
        $piece10->setReference('ME-JOI-001');
        $piece10->setNom('Joint torique 50x5');
        $piece10->setDescription('Joint NBR, diamètre 50mm, section 5mm');
        $piece10->setPrixUnitaire(8.00);
        $piece10->setQuantiteStock(100);
        $piece10->setSeuilAlerte(30);
        $piece10->setEmplacement('Tiroir F1');
        $piece10->setFournisseur($fournisseur2);
        $manager->persist($piece10);

        // =============================================
        // MACHINES
        // =============================================

        $machine1 = new Machine();
        $machine1->setReference('MCH-2024-001');
        $machine1->setModele('CNC-5000');
        $machine1->setMarque('Haas');
        $machine1->setType('Centre d\'usinage CNC');
        $machine1->setDateAcquisition(new \DateTime('2022-03-15'));
        $machine1->setStatut('En service');
        $machine1->setClient($client1);
        $manager->persist($machine1);

        $machine2 = new Machine();
        $machine2->setReference('MCH-2024-002');
        $machine2->setModele('TL-2000');
        $machine2->setMarque('Mazak');
        $machine2->setType('Tour CNC');
        $machine2->setDateAcquisition(new \DateTime('2021-08-20'));
        $machine2->setStatut('En service');
        $machine2->setClient($client1);
        $manager->persist($machine2);

        $machine3 = new Machine();
        $machine3->setReference('MCH-2024-003');
        $machine3->setModele('HP-300');
        $machine3->setMarque('Trumpf');
        $machine3->setType('Presse hydraulique');
        $machine3->setDateAcquisition(new \DateTime('2020-05-10'));
        $machine3->setStatut('En maintenance');
        $machine3->setClient($client2);
        $manager->persist($machine3);

        $machine4 = new Machine();
        $machine4->setReference('MCH-2024-004');
        $machine4->setModele('WJ-4020');
        $machine4->setMarque('Flow');
        $machine4->setType('Découpe jet d\'eau');
        $machine4->setDateAcquisition(new \DateTime('2023-01-25'));
        $machine4->setStatut('En service');
        $machine4->setClient($client2);
        $manager->persist($machine4);

        $machine5 = new Machine();
        $machine5->setReference('MCH-2024-005');
        $machine5->setModele('RM-500');
        $machine5->setMarque('Kasto');
        $machine5->setType('Scie à ruban');
        $machine5->setDateAcquisition(new \DateTime('2019-11-30'));
        $machine5->setStatut('En service');
        $machine5->setClient($client3);
        $manager->persist($machine5);

        $machine6 = new Machine();
        $machine6->setReference('MCH-2024-006');
        $machine6->setModele('GR-1500');
        $machine6->setMarque('Jones & Shipman');
        $machine6->setType('Rectifieuse plane');
        $machine6->setDateAcquisition(new \DateTime('2018-07-12'));
        $machine6->setStatut('Hors service');
        $machine6->setClient($client3);
        $manager->persist($machine6);

        $machine7 = new Machine();
        $machine7->setReference('MCH-2024-007');
        $machine7->setModele('BR-2500');
        $machine7->setMarque('Amada');
        $machine7->setType('Plieuse hydraulique');
        $machine7->setDateAcquisition(new \DateTime('2022-09-05'));
        $machine7->setStatut('En service');
        $machine7->setClient($client4);
        $manager->persist($machine7);

        $machine8 = new Machine();
        $machine8->setReference('MCH-2024-008');
        $machine8->setModele('LS-3000');
        $machine8->setMarque('Bystronic');
        $machine8->setType('Découpe laser');
        $machine8->setDateAcquisition(new \DateTime('2023-06-18'));
        $machine8->setStatut('En service');
        $machine8->setClient($client4);
        $manager->persist($machine8);

        $machine9 = new Machine();
        $machine9->setReference('MCH-2024-009');
        $machine9->setModele('TX-800');
        $machine9->setMarque('Picanol');
        $machine9->setType('Métier à tisser');
        $machine9->setDateAcquisition(new \DateTime('2021-02-28'));
        $machine9->setStatut('En service');
        $machine9->setClient($client5);
        $manager->persist($machine9);

        $machine10 = new Machine();
        $machine10->setReference('MCH-2024-010');
        $machine10->setModele('TX-800');
        $machine10->setMarque('Picanol');
        $machine10->setType('Métier à tisser');
        $machine10->setDateAcquisition(new \DateTime('2021-02-28'));
        $machine10->setStatut('En service');
        $machine10->setClient($client5);
        $manager->persist($machine10);

        // =============================================
        // PANNES (Breakdowns)
        // =============================================

        $panne1 = new Panne();
        $panne1->setMachine($machine3);
        $panne1->setDateDeclaration(new \DateTime('2025-12-20 09:30:00'));
        $panne1->setDescription('Fuite d\'huile importante au niveau du vérin principal. Pression insuffisante.');
        $panne1->setGravite('Elevee');
        $panne1->setStatut('En traitement');
        $manager->persist($panne1);

        $panne2 = new Panne();
        $panne2->setMachine($machine6);
        $panne2->setDateDeclaration(new \DateTime('2025-12-15 14:00:00'));
        $panne2->setDescription('Moteur de broche grillé. Machine à l\'arrêt complet.');
        $panne2->setGravite('Elevee');
        $panne2->setStatut('Declaree');
        $manager->persist($panne2);

        $panne3 = new Panne();
        $panne3->setMachine($machine1);
        $panne3->setDateDeclaration(new \DateTime('2025-12-28 11:15:00'));
        $panne3->setDescription('Alarme erreur servo axe X. Vibrations anormales.');
        $panne3->setGravite('Moyenne');
        $panne3->setStatut('Resolue');
        $manager->persist($panne3);

        $panne4 = new Panne();
        $panne4->setMachine($machine5);
        $panne4->setDateDeclaration(new \DateTime('2025-12-30 08:45:00'));
        $panne4->setDescription('Lame de scie cassée, guide-lame endommagé.');
        $panne4->setGravite('Faible');
        $panne4->setStatut('Resolue');
        $manager->persist($panne4);

        $panne5 = new Panne();
        $panne5->setMachine($machine8);
        $panne5->setDateDeclaration(new \DateTime('2026-01-02 16:30:00'));
        $panne5->setDescription('Problème d\'alignement du faisceau laser. Découpes imprécises.');
        $panne5->setGravite('Moyenne');
        $panne5->setStatut('Declaree');
        $manager->persist($panne5);

        // =============================================
        // INTERVENTIONS
        // =============================================

        // Intervention terminée 1
        $intervention1 = new Intervention();
        $intervention1->setMachine($machine1);
        $intervention1->setTechnicien($tech1);
        $intervention1->setType('corrective');
        $intervention1->setPriorite('Elevee');
        $intervention1->setStatut('Terminee');
        $intervention1->setDateDebut(new \DateTime('2025-12-28 14:00:00'));
        $intervention1->setDateFinPrevue(new \DateTime('2025-12-28 18:00:00'));
        $intervention1->setDateFinReelle(new \DateTime('2025-12-28 17:30:00'));
        $intervention1->setDuree('3h 30m');
        $intervention1->setDescription('Réparation servo axe X suite à alarme erreur');
        $intervention1->setResolution('Remplacement du roulement du moteur servo et recalibration de l\'axe');
        $intervention1->setCoutMainOeuvre(175.00);
        $intervention1->setCoutPieces(85.00);
        $intervention1->setCoutTotal(260.00);
        $intervention1->setTauxHoraireApplique(50.0);
        $intervention1->setConfirmationTechnicien(true);
        $intervention1->setConfirmationTechnicienAt(new \DateTime('2025-12-28 17:35:00'));
        $intervention1->setConfirmationClient(true);
        $intervention1->setConfirmationClientAt(new \DateTime('2025-12-28 17:45:00'));
        $intervention1->setSignerNom('Ahmed Tazi');
        $manager->persist($intervention1);

        // Link panne3 to intervention1
        $panne3->setIntervention($intervention1);

        // Intervention terminée 2
        $intervention2 = new Intervention();
        $intervention2->setMachine($machine5);
        $intervention2->setTechnicien($tech2);
        $intervention2->setType('corrective');
        $intervention2->setPriorite('Normale');
        $intervention2->setStatut('Terminee');
        $intervention2->setDateDebut(new \DateTime('2025-12-30 09:00:00'));
        $intervention2->setDateFinPrevue(new \DateTime('2025-12-30 12:00:00'));
        $intervention2->setDateFinReelle(new \DateTime('2025-12-30 11:30:00'));
        $intervention2->setDuree('2h 30m');
        $intervention2->setDescription('Remplacement lame et guide-lame');
        $intervention2->setResolution('Installation nouvelle lame et réglage du guide. Test de coupe effectué.');
        $intervention2->setCoutMainOeuvre(137.50);
        $intervention2->setCoutPieces(250.00);
        $intervention2->setCoutTotal(387.50);
        $intervention2->setTauxHoraireApplique(55.0);
        $intervention2->setConfirmationTechnicien(true);
        $intervention2->setConfirmationTechnicienAt(new \DateTime('2025-12-30 11:35:00'));
        $intervention2->setConfirmationClient(true);
        $intervention2->setConfirmationClientAt(new \DateTime('2025-12-30 11:50:00'));
        $intervention2->setSignerNom('Khalid Mansouri');
        $manager->persist($intervention2);

        // Link panne4 to intervention2
        $panne4->setIntervention($intervention2);

        // Intervention en cours
        $intervention3 = new Intervention();
        $intervention3->setMachine($machine3);
        $intervention3->setTechnicien($tech3);
        $intervention3->setType('corrective');
        $intervention3->setPriorite('Urgente');
        $intervention3->setStatut('En cours');
        $intervention3->setDateDebut(new \DateTime('2026-01-02 08:00:00'));
        $intervention3->setDateFinPrevue(new \DateTime('2026-01-03 18:00:00'));
        $intervention3->setDescription('Réparation fuite hydraulique vérin principal');
        $intervention3->setTauxHoraireApplique(60.0);
        $intervention3->setConfirmationTechnicien(false);
        $intervention3->setConfirmationClient(false);
        $manager->persist($intervention3);

        // Link panne1 to intervention3
        $panne1->setIntervention($intervention3);

        // Intervention préventive planifiée
        $intervention4 = new Intervention();
        $intervention4->setMachine($machine2);
        $intervention4->setTechnicien($tech1);
        $intervention4->setType('preventive');
        $intervention4->setPriorite('Normale');
        $intervention4->setStatut('En attente');
        $intervention4->setDateDebut(new \DateTime('2026-01-05 08:00:00'));
        $intervention4->setDateFinPrevue(new \DateTime('2026-01-05 12:00:00'));
        $intervention4->setDescription('Maintenance préventive trimestrielle: vérification niveaux, lubrification, contrôle alignement');
        $intervention4->setTauxHoraireApplique(50.0);
        $intervention4->setConfirmationTechnicien(false);
        $intervention4->setConfirmationClient(false);
        $manager->persist($intervention4);

        // Intervention préventive 2
        $intervention5 = new Intervention();
        $intervention5->setMachine($machine7);
        $intervention5->setTechnicien($tech2);
        $intervention5->setType('preventive');
        $intervention5->setPriorite('Normale');
        $intervention5->setStatut('En attente');
        $intervention5->setDateDebut(new \DateTime('2026-01-06 14:00:00'));
        $intervention5->setDateFinPrevue(new \DateTime('2026-01-06 17:00:00'));
        $intervention5->setDescription('Contrôle annuel presse plieuse: vérification sécurités, calibration jauges');
        $intervention5->setTauxHoraireApplique(55.0);
        $intervention5->setConfirmationTechnicien(false);
        $intervention5->setConfirmationClient(false);
        $manager->persist($intervention5);

        // =============================================
        // PIECES INTERVENTION (Parts used)
        // =============================================

        $pieceInt1 = new PieceIntervention();
        $pieceInt1->setPiece($piece3);
        $pieceInt1->setIntervention($intervention1);
        $pieceInt1->setQuantite(1);
        $pieceInt1->setPrixUnitaireApplique(85.00);
        $pieceInt1->setDateUtilisation(new \DateTime('2025-12-28 16:00:00'));
        $manager->persist($pieceInt1);

        $pieceInt2 = new PieceIntervention();
        $pieceInt2->setPiece($piece6);
        $pieceInt2->setIntervention($intervention3);
        $pieceInt2->setQuantite(2);
        $pieceInt2->setPrixUnitaireApplique(120.00);
        $pieceInt2->setDateUtilisation(new \DateTime('2026-01-02 10:30:00'));
        $manager->persist($pieceInt2);

        $pieceInt3 = new PieceIntervention();
        $pieceInt3->setPiece($piece10);
        $pieceInt3->setIntervention($intervention3);
        $pieceInt3->setQuantite(5);
        $pieceInt3->setPrixUnitaireApplique(8.00);
        $pieceInt3->setDateUtilisation(new \DateTime('2026-01-02 11:00:00'));
        $manager->persist($pieceInt3);

        // =============================================
        // INTERVENTION LOGS
        // =============================================

        $log1 = new InterventionLog();
        $log1->setIntervention($intervention1);
        $log1->setUser($userTech1);
        $log1->setMessage('Arrivée sur site. Diagnostic en cours.');
        $log1->setType('arrival');
        $manager->persist($log1);

        $log2 = new InterventionLog();
        $log2->setIntervention($intervention1);
        $log2->setUser($userTech1);
        $log2->setMessage('Roulement défectueux identifié. Commande pièce en stock.');
        $log2->setType('comment');
        $manager->persist($log2);

        $log3 = new InterventionLog();
        $log3->setIntervention($intervention1);
        $log3->setUser($userTech1);
        $log3->setMessage('Remplacement effectué. Tests en cours.');
        $log3->setType('comment');
        $manager->persist($log3);

        $log4 = new InterventionLog();
        $log4->setIntervention($intervention1);
        $log4->setUser($userTech1);
        $log4->setMessage('Intervention terminée. Machine opérationnelle.');
        $log4->setType('status_change');
        $manager->persist($log4);

        $log5 = new InterventionLog();
        $log5->setIntervention($intervention3);
        $log5->setUser($userTech3);
        $log5->setMessage('Début intervention. Vidange circuit hydraulique en cours.');
        $log5->setType('arrival');
        $manager->persist($log5);

        $log6 = new InterventionLog();
        $log6->setIntervention($intervention3);
        $log6->setUser($userTech3);
        $log6->setMessage('Joints du vérin remplacés. En attente de remplissage huile.');
        $log6->setType('comment');
        $manager->persist($log6);

        // =============================================
        // MOUVEMENTS STOCK
        // =============================================

        // Entrée de stock
        $mvt1 = new MouvementStock();
        $mvt1->setPiece($piece3);
        $mvt1->setType('entree');
        $mvt1->setQuantite(20);
        $mvt1->setQuantiteAvant(31);
        $mvt1->setQuantiteApres(51);
        $mvt1->setMotif('Réception commande fournisseur #F2025-089');
        $manager->persist($mvt1);

        // Sortie pour intervention
        $mvt2 = new MouvementStock();
        $mvt2->setPiece($piece3);
        $mvt2->setType('sortie');
        $mvt2->setQuantite(1);
        $mvt2->setQuantiteAvant(51);
        $mvt2->setQuantiteApres(50);
        $mvt2->setMotif('Intervention #1 - Machine MCH-2024-001');
        $manager->persist($mvt2);

        $mvt3 = new MouvementStock();
        $mvt3->setPiece($piece6);
        $mvt3->setType('sortie');
        $mvt3->setQuantite(2);
        $mvt3->setQuantiteAvant(22);
        $mvt3->setQuantiteApres(20);
        $mvt3->setMotif('Intervention #3 - Machine MCH-2024-003');
        $manager->persist($mvt3);

        $mvt4 = new MouvementStock();
        $mvt4->setPiece($piece10);
        $mvt4->setType('sortie');
        $mvt4->setQuantite(5);
        $mvt4->setQuantiteAvant(105);
        $mvt4->setQuantiteApres(100);
        $mvt4->setMotif('Intervention #3 - Machine MCH-2024-003');
        $manager->persist($mvt4);

        // Ajustement inventaire
        $mvt5 = new MouvementStock();
        $mvt5->setPiece($piece7);
        $mvt5->setType('ajustement');
        $mvt5->setQuantite(30);
        $mvt5->setQuantiteAvant(28);
        $mvt5->setQuantiteApres(30);
        $mvt5->setMotif('Correction inventaire annuel');
        $manager->persist($mvt5);

        $mvt6 = new MouvementStock();
        $mvt6->setPiece($piece1);
        $mvt6->setType('entree');
        $mvt6->setQuantite(5);
        $mvt6->setQuantiteAvant(3);
        $mvt6->setQuantiteApres(8);
        $mvt6->setMotif('Réception commande fournisseur #F2025-092');
        $manager->persist($mvt6);

        // =============================================
        // FLUSH ALL
        // =============================================

        $manager->flush();
    }
}
