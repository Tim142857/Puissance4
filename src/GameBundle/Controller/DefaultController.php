<?php

namespace GameBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use GameBundle\Entity\Grid;
use GameBundle\Entity\Ligne;
use GameBundle\Entity\Colonne;
use GameBundle\Entity\Slot;
use GameBundle\Entity\Player;
use Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\JsonResponse;
use \Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Serializer;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('GameBundle::index.html.twig');
    }

    public function gameAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $repoGrids = $em->getRepository('GameBundle:Grid');
        $repoColonnes = $em->getRepository('GameBundle:Colonne');
        $repoPlayers = $em->getRepository('GameBundle:Player');

        $height = 6;
        $width = 7;

        if ($id == null) {

            $grid = new Grid();
            $em->persist($grid);
            $em->flush();

            $player1 = new Player();
            $player1->setName('Toto');
            $player1->setColor('red');
            $player1->setGrid($grid);
            $em->persist($player1);
            $em->flush();

//
            $player2 = $repoPlayers->findOneById(1);
            $player2->setGrid($grid);
            $em->persist($player2);
            $em->flush();

            $grid->setNextPlayer($player1);


            $idFirstColonne = 0;
            for ($i = 0; $i < $height; $i++) {
                $ligne = new Ligne();
                $ligne->setGrid($grid);
                $em->persist($ligne);
                $em->flush();
                for ($j = 0; $j < $width; $j++) {
                    if ($i == 0) {
                        $colonne = new Colonne();
                        $colonne->setGrid($grid);
                        $em->persist($colonne);
                        $em->flush();
                    }
                    if ($i == 0 && $j == 0) {
                        $idFirstColonne = $colonne->getId();
                    }
                    $slot = new Slot();
                    $slot->setLigne($ligne);
                    $actualColonne = $repoColonnes->findOneById($idFirstColonne + $j);
                    $slot->setColonne($actualColonne);
                    $em->persist($slot);
                    $em->flush();

                }
            }

        } else {
            $grid = $repoGrids->findOneById($id);
        }

        return $this->render('GameBundle::game.html.twig', array('grid' => $grid));
    }

    public function coupAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $repoColonne = $em->getRepository('GameBundle:Colonne');
        $repoPlayer = $em->getRepository('GameBundle:Player');
        $idColonne = filter_input(INPUT_POST, 'colonne');
        $idPlayer = filter_input(INPUT_POST, 'player');
        if ($idColonne == null && $idPlayer == 1) {
            //traitement par l'IA
            dump('IA!');
        } else {

            //Ajout du jeton dans la colonne
            $colonne = $repoColonne->findOneById($idColonne);

            foreach ($colonne->getSlots() as $slot) {
                if ($slot->getPlayer() == null) {
                    $slotToEdit = $slot;
                }
            }
            $player = $repoPlayer->findOneById($idPlayer);
            $slotToEdit->setPlayer($player);
            $em->persist($slotToEdit);
            $em->flush();

            //Verification si victoire
            $finJeu = $this->finJeu($player->getGrid());

            $grid = $player->getGrid();
            foreach ($grid->getPlayers() as $player3) {
                if ($player3 != $player) {
                    $player2 = $player3;
                    $grid->setNextPlayer($player2);
                    $em->persist($grid);
                    $em->flush();
                }

            }

            $slots = $this->finJeu($player->getGrid());

            $array['idslot'] = $slotToEdit->getId();
            $array['color'] = $player->getColor();
            $array['nextPlayerName'] = $player2->getName();
            $array['nextPlayerId'] = $player2->getId();
            $array['finJeu'] = $finJeu['finJeu'];
            if (isset($finJeu['idGagnant'])) {
                $array['idGagnant'] = $finJeu['idGagnant'];
                $array['nomGagnant'] = $finJeu['nomGagnant'];
            }


        }

        return new JsonResponse($array);
    }

    private function finJeu($grid)
    {
        $finJeu['finJeu'] = true;

        $em = $this->getDoctrine()->getManager();
        $repoSlot = $em->getRepository('GameBundle:Slot');
        $repoColonne = $em->getRepository('GameBundle:Colonne');
        $colonnes = $repoColonne->findByGrid($grid);
        $repoLigne = $em->getRepository('GameBundle:Ligne');
        $lignes = $repoLigne->findByGrid($grid);

        $slots = array();
        foreach ($colonnes as $colonne) {
            foreach ($colonne->getSlots() as $slot) {
                array_push($slots, $slot);
            }
        }

        //Verification des lignes s'il y a un gagnant
        foreach ($lignes as $ligne) {
            for ($i = 0; $i < (count($ligne->getSlots()) - 4); $i++) {
                $arraySlots = array_reverse($ligne->getSlots()->getValues());
                $player1 = $arraySlots[$i]->getPlayer();
                $player2 = $arraySlots[$i + 1]->getPlayer();
                $player3 = $arraySlots[$i + 2]->getPlayer();
                $player4 = $arraySlots[$i + 3]->getPlayer();
                if ($player1 != null && $player2 != null && $player3 != null && $player4 != null) {
                    if ($player1 == $player2 && $player2 == $player3 && $player3 == $player4) {
                        $finJeu['idGagnant'] = $player1->getId();
                    }
                }
            }
        }

        //Verification des colonnes s'il y a un gagnant
        foreach ($colonnes as $colonne) {
            for ($i = 0; $i < (count($colonne->getSlots()) - 4); $i++) {
                $arraySlots = array_reverse($colonne->getSlots()->getValues());
                $player1 = $arraySlots[$i]->getPlayer();
                $player2 = $arraySlots[$i + 1]->getPlayer();
                $player3 = $arraySlots[$i + 2]->getPlayer();
                $player4 = $arraySlots[$i + 3]->getPlayer();
                if ($player1 != null && $player2 != null && $player3 != null && $player4 != null) {
                    if ($player1 == $player2 && $player2 == $player3 && $player3 == $player4) {
                        $finJeu['idGagnant'] = $player1->getId();
                    }
                }
            }
        }

        //Verification des diagonales s'il y a un gagnant
        foreach ($slots as $slot) {
            if ($slot->getId() % 7 <= 4) {
                $player1 = $slot->getPlayer();
                $slot2 = $repoSlot->findOneById(($slot->getId() + 6));
                $slot3 = $repoSlot->findOneById(($slot->getId() + 12));
                $slot4 = $repoSlot->findOneById(($slot->getId() + 18));
                $player2 = $slot2 == null ? null : $slot2->getPlayer();
                $player3 = $slot3 == null ? null : $slot3->getPlayer();
                $player4 = $slot4 == null ? null : $slot4->getPlayer();
                if ($player1 != null && $player2 != null && $player3 != null && $player4 != null) {
                    if ($player1 == $player2 && $player2 == $player3 && $player3 == $player4) {
                        $finJeu['idGagnant'] = $player1->getId();
                    }
                }
            }
        }
        foreach ($slots as $slot) {
            if ($slot->getId() % 7 > 4) {
                $player1 = $slot->getPlayer();
                $slot2 = $repoSlot->findOneById(($slot->getId() + 8));
                $slot3 = $repoSlot->findOneById(($slot->getId() + 16));
                $slot4 = $repoSlot->findOneById(($slot->getId() + 24));
                $player2 = $slot2 == null ? null : $slot2->getPlayer();
                $player3 = $slot3 == null ? null : $slot3->getPlayer();
                $player4 = $slot4 == null ? null : $slot4->getPlayer();
                if ($player1 != null && $player2 != null && $player3 != null && $player4 != null) {
                    if ($player1 == $player2 && $player2 == $player3 && $player3 == $player4) {
                        $finJeu['idGagnant'] = $player1->getId();
                    }
                }
            }
        }


        //S'il y a un gagnant, récupération de son nom
        if (isset($finJeu['idGagnant'])) {
            $finJeu['nomGagnant'] = $em->getRepository('GameBundle:Player')->findOneById($finJeu['idGagnant'])->getName();
        }


        //S'il n'y a pas de gagnant, vérification de l'égalité
        if (!isset($finJeu['idGagnant'])) {
            foreach ($slots as $slot) {
                if ($slot->getPlayer() == null) {
                    $finJeu['finJeu'] = false;
                }
            }
        }

        return $finJeu;

    }
}
