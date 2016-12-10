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

//            $player2 = new Player();
//            $player2->setName('Titi');
//            $player2->setColor('orange');
//            $player2->setGrid($grid);
//            $em->persist($player2);
//            $em->flush();

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
        $repoGrid = $em->getRepository('GameBundle:Grid');
        $idColonne = filter_input(INPUT_POST, 'colonne');
        $idPlayer = filter_input(INPUT_POST, 'player');

        if ($idColonne == 'null' && $idPlayer == '1') {
            $grid = $repoGrid->findOneById(filter_input(INPUT_POST, 'idgrid'));
            //traitement par l'IA
//            dump('IA!');
            $idColonne = $this->retourneRandomIdColonne($grid);

        }

        //Ajout du jeton dans la colonne
        $colonne = $repoColonne->findOneById($idColonne);

        foreach ($colonne->getSlots() as $slot) {
            if ($slot->getPlayer() == null) {
                $slotToEdit = $slot;
            }
        }

        //Si colonne déja remplie
        if (!isset($slotToEdit)) {
            return new JsonResponse(array("error" => "colonne déja remplie!"));
        }


        $player = $repoPlayer->findOneById($idPlayer);

        dump($this->getFullSlots($player->getGrid()));
        dump($this->genereTours($player->getGrid(), $player, 0));

        $slotToEdit->setPlayer($player);
        $em->persist($slotToEdit);
        $em->flush();


        //Verification si victoire
        $finJeu = $this->finJeu($player->getGrid());

        $grid = $player->getGrid();
        $player2 = '';
        foreach ($grid->getPlayers() as $player3) {
            if ($player3 != $player) {
                $player2 = $player3;
                $grid->setNextPlayer($player2);
                $em->persist($grid);
                $em->flush();
            }

        }

//        $slots = $this->finJeu($player->getGrid());

        $array['idslot'] = $slotToEdit->getId();
        $array['color'] = $player->getColor();
        $array['nextPlayerName'] = $player2->getName();
        $array['nextPlayerId'] = $player2->getId();
        $array['finJeu'] = $finJeu['finJeu'];
        if (isset($finJeu['idGagnant'])) {
            $array['idGagnant'] = $finJeu['idGagnant'];
            $array['nomGagnant'] = $finJeu['nomGagnant'];
        }


        return new JsonResponse($array);
    }

    function finJeu($grid)
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
        $results = $this->getSlotsAlignedByLines($grid, 4);
//        dump($results);
        foreach ($results as $id => $count) {
            if ($count > 0) {
                $finJeu['idGagnant'] = $id;
            }
        }
        //Verification des colonnes s'il y a un gagnant
//        if (!isset($finJeu['idGagnant'])) {
        $results = $this->getSlotsAlignedByColonnes($grid, 4);
//        dump($results);
        foreach ($results as $id => $count) {
            if ($count > 0) {
                $finJeu['idGagnant'] = $id;
            }
        }
//        }


        //Verification des diagonales s'il y a un gagnant


        $results = $this->getSlotsAlignedByDiagonales($grid, 4);
//        dump($results);
//        if (!isset($finJeu['idGagnant'])) {
        foreach ($results as $id => $count) {
            if ($count > 0) {
                $finJeu['idGagnant'] = $id;
            }
        }
//    }


//        foreach ($slots as $slot) {
////            if ($slot->getId() % 7 <= 4) {
//            $player1 = $slot->getPlayer();
//            $slot2 = $repoSlot->findOneById(($slot->getId() - 6));
//            $slot3 = $repoSlot->findOneById(($slot->getId() - 12));
//            $slot4 = $repoSlot->findOneById(($slot->getId() - 18));
//            $player2 = $slot2 == null ? null : $slot2->getPlayer();
//            $player3 = $slot3 == null ? null : $slot3->getPlayer();
//            $player4 = $slot4 == null ? null : $slot4->getPlayer();
//            if ($player1 != null && $player2 != null && $player3 != null && $player4 != null) {
//                if ($player1 == $player2 && $player2 == $player3 && $player3 == $player4) {
//                    $finJeu['idGagnant'] = $player1->getId();
//                    dump('1er cas: /');
//                }
//            }
////            }
//        }

//        foreach ($slots as $slot) {
////            if ($slot->getId() % 7 >= 4) {
//            $player1 = $slot->getPlayer();
//            $slot2 = $repoSlot->findOneById(($slot->getId() + 8));
//            $slot3 = $repoSlot->findOneById(($slot->getId() + 16));
//            $slot4 = $repoSlot->findOneById(($slot->getId() + 24));
//            $player2 = $slot2 == null ? null : $slot2->getPlayer();
//            $player3 = $slot3 == null ? null : $slot3->getPlayer();
//            $player4 = $slot4 == null ? null : $slot4->getPlayer();
//            if ($player1 != null && $player2 != null && $player3 != null && $player4 != null) {
//                if ($player1 == $player2 && $player2 == $player3 && $player3 == $player4) {
//                    $finJeu['idGagnant'] = $player1->getId();
//                    dump('2e cas: \\');
//                }
//            }
////            }
//        }
//
//
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

    function getSlotsAlignedByLines($grid, $long)
    {
        $em = $this->getDoctrine()->getManager();
        $repoLigne = $em->getRepository('GameBundle:Ligne');
        $lignes = $repoLigne->findByGrid($grid);
        $results = array();

        //Verification des lignes s'il y a un gagnant
        foreach ($lignes as $ligne) {
            for ($i = 0; $i < (count($ligne->getSlots()) - $long); $i++) {
                $arraySlots = $ligne->getSlots();
                $players = array();
                for ($j = 0; $j < $long; $j++) {
                    array_push($players, $arraySlots[$j + $i]->getPlayer());
                }
                $array = array_filter($players, function ($value) use ($players) {
                    if ($value == null) {
                        return false;
                    }
                    return ($value == $players[0]);
                });
                if (count($array) == $long) {
//                    dump('win ligne ' . $ligne->getId());
                    if (isset($results[$players[0]->getId()])) {
                        $results[$players[0]->getId()]++;
                    } else {
                        $results[$players[0]->getId()] = 1;
                    }
                }
            }
        }
        return $results;
    }

    function getSlotsAlignedByColonnes($grid, $long)
    {
        $em = $this->getDoctrine()->getManager();
        $repoColonne = $em->getRepository('GameBundle:Colonne');
        $colonnes = $repoColonne->findByGrid($grid);
        $results = array();

        //Verification des colonnes s'il y a un gagnant
        foreach ($colonnes as $colonne) {
            for ($i = 0; $i < (count($colonne->getSlots()) - $long); $i++) {
                $arraySlots = $colonne->getSlots()->getValues();
                $players = array();
                for ($j = 0; $j < $long; $j++) {
                    array_push($players, $arraySlots[$j + $i]->getPlayer());
                }
                $array = array_filter($players, function ($value) use ($players) {
                    if ($value == null) {
                        return false;
                    }
                    return ($value == $players[0]);
                });
                if (count($array) == $long) {
//                    dump('win colonne ' . $colonne->getId());
                    if (isset($results[$players[0]->getId()])) {
                        $results[$players[0]->getId()]++;
                    } else {
                        $results[$players[0]->getId()] = 1;
                    }
                }
            }
        }
        return $results;
    }

    function getSlotsAlignedByDiagonales($grid, $long)
    {
        $em = $this->getDoctrine()->getManager();
        $repoColonne = $em->getRepository('GameBundle:Colonne');
        $repoSlot = $em->getRepository('GameBundle:Slot');
        $colonnes = $repoColonne->findByGrid($grid);
        $results = array();

        $slots = array();
        foreach ($colonnes as $colonne) {
            foreach ($colonne->getSlots() as $slot) {
                array_push($slots, $slot);
            }
        }

        foreach ($slots as $slot) {
            $players = array();
            for ($j = 0; $j < $long; $j++) {
                $var = 'slot' . $j;
                $$var = $repoSlot->findOneById(($slot->getId() - $j * 6));
                if ($$var != null)
                    array_push($players, $$var->getPlayer());
            }
            $array = array_filter($players, function ($value) use ($players) {
                if ($value == null)
                    return false;
                return $value == $players[0];
            });
            if (count($array) == $long) {
//                dump('win / slot ' . $slot->getId());
                if (isset($results[$players[0]->getId()])) {
                    $results[$players[0]->getId()]++;
                } else {
                    $results[$players[0]->getId()] = 1;
                }
            }

            //----------------------------------------------------
            $players = array();
            for ($j = 0; $j < $long; $j++) {
                $var = 'slot' . $j;
                $$var = $repoSlot->findOneById(($slot->getId() - $j * 8));
                if ($$var != null)
                    array_push($players, $$var->getPlayer());
            }
            $array = array_filter($players, function ($value) use ($players) {
                if ($value == null)
                    return false;
                return $value == $players[0];
            });
//            dump($players);
            if (count($array) == $long) {
//                dump('win \\ slot ' . $slot->getId());
                if (isset($results[$players[0]->getId()])) {
                    $results[$players[0]->getId()]++;
                } else {
                    $results[$players[0]->getId()] = 1;
                }
            }
        }

        return $results;

    }

    function retourneRandomIdColonne($grid)
    {
        //Test
        $em = $this->getDoctrine()->getManager();
        $repoPlayer = $em->getRepository('GameBundle:Player');
        $player = $repoPlayer->findOneById(1);


        //-----------
        $colonnes = $grid->getColonnes();
        return $colonnes[0]->getId();
        $colonne = '';
        do {
            $full = true;
            $colonne = $colonnes[rand(1, count($colonnes))];
            foreach ($colonne->getSlots() as $slot) {
                if ($slot->getPlayer() == null) {
                    $full = false;
                }
            }
        } while ($full);
        return $colonne->getId();

    }

    function getValeurGrid($grid, $player)
    {

        $value = 0;
        //test si gagne ou perdu ou égalité(+1000/-1000/0)
        $result = $this->finJeu($grid);
        if (isset($result['idGagnant'])) {
            if ($result['idGagnant'] == $player->getId()) {
                $value += 1000;
            } else {
                $value -= 1000;
            }
            return $value;
        }

        return $value;

        //test si 3 pions alignés avec libre de chaque coté(+1000)

        //test si 3 pions alignés(+300)

        //test si 2 pions alignés libres de chaque côté(+100)


    }

    function genereTours($grid, $player, $depth)
    {
        dump($grid);
        $em = $this->getDoctrine()->getManager();
        $repoPlayer = $em->getRepository('GameBundle:Player');
        $grids = array();
        $originalGrid = $grid;

        //Je génère tous mes coups

        //parcours des colonnes
//        foreach ($grid->getColonnes() as $colonne) {
        for ($i = 0; $i < count($grid->getColonnes()); $i++) {
            $newGrid = new Grid();
            $newGrid = $originalGrid;
            dump('debutBoucle');
            dump($this->getCountFullSlots($newGrid));
            $colonne = $newGrid->getColonnes()[$i];
            //Je récupère le premier slot vide
            foreach ($colonne->getSlots() as $slot) {
                if ($slot->getPlayer() == null) {
                    $slotToEdit = $slot;
                }
            }
            //Si colonne déja remplie
            if (!isset($slotToEdit)) {
                break;
            }

            //J'edite le slot
            $slotToEdit->setPlayer($player);

            //j'enregistre la nouvelle grille dans l'array
//            array_push($grids, $newGrid);
//            dump($this->getFullSlots($newGrid));
            $grids[$colonne->getId()] = $newGrid;
            dump($this->getCountFullSlots($newGrid));
            $slotToEdit->setPlayer(null);
        }
//        dump($grids);

        //Je trie mon tableau par ordre croissant
        $newGrids = $grids;
//        $newGrids = uasort($grids, function ($a, $b) use ($player) {
//            $aValue = $this->getValeurGrid($a, $player);
//            $bValue = $this->getValeurGrid($b, $player);
//            if ($aValue == $bValue) {
//                return 0;
//            }
//            return ($aValue < $bValue) ? -1 : 1;
//        });
//        dump($newGrids);
        $id = 0;
        $value = -10000;
        $repoSlot = $em->getRepository('GameBundle:Slot');
        foreach ($grids as $key => $gridd) {
            dump($this->getCountFullSlots($gridd));
            $newValue = $this->getValeurGrid($gridd, $player);
            dump($key . '/' . $newValue);
//            dump($gridd);
            if ($newValue > $value) {
                $id = $key;
                $value = $newValue;
            }
        }
        return [$id, $value];

//        if ($player->getId() == 1) {
//            $slotToReturn = '';
//            foreach ($newGrids as $slot => $gridd) {
//                $slotToReturn = $slot;
//            }
//            return $slotToReturn;
//        } else {
//            foreach ($newGrids as $slot => $gridd) {
//                return $slot;
//            }
//        }
    }

    function getFullSlots($grid)
    {
        $fullSlots = array();
        foreach ($grid->getColonnes() as $colonne) {
            foreach ($colonne->getSlots() as $slot) {
                if ($slot->getPlayer() != null) {
                    array_push($fullSlots, $slot->getId());
                }
            }
        }
        return $fullSlots;
    }

    function getCountFullSlots($grid)
    {
        $count = 0;
        foreach ($grid->getColonnes() as $colonne) {
            foreach ($colonne->getSlots() as $slot) {
                if ($slot->getPlayer() != null) {
                    $count++;
                }
            }
        }
        return $count;
    }
}
