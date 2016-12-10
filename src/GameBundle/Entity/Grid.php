<?php

namespace GameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Grid
 *
 * @ORM\Table(name="grid")
 * @ORM\Entity(repositoryClass="GameBundle\Repository\GridRepository")
 */
class Grid
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="GameBundle\Entity\Colonne", mappedBy="grid", cascade={"remove", "persist"})
     */
    private $colonnes;

    /**
     * @ORM\OneToMany(targetEntity="GameBundle\Entity\Ligne", mappedBy="grid", cascade={"remove", "persist"})
     */
    private $lignes;

    /**
     * @ORM\OneToMany(targetEntity="GameBundle\Entity\Player", mappedBy="grid", cascade={"remove", "persist"})
     */
    private $players;

    /**
     * @var \Player
     *
     * @ORM\ManyToOne(targetEntity="Player")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="Player", referencedColumnName="id")
     * })
     */
    private $nextPlayer;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->colonnes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->lignes = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add colonne
     *
     * @param \GameBundle\Entity\Colonne $colonne
     *
     * @return Grid
     */
    public function addColonne(\GameBundle\Entity\Colonne $colonne)
    {
        $this->colonnes[] = $colonne;

        return $this;
    }

    /**
     * Remove colonne
     *
     * @param \GameBundle\Entity\Colonne $colonne
     */
    public function removeColonne(\GameBundle\Entity\Colonne $colonne)
    {
        $this->colonnes->removeElement($colonne);
    }

    /**
     * Get colonnes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getColonnes()
    {
        return $this->colonnes;
    }

    /**
     * Add ligne
     *
     * @param \GameBundle\Entity\Ligne $ligne
     *
     * @return Grid
     */
    public function addLigne(\GameBundle\Entity\Ligne $ligne)
    {
        $this->lignes[] = $ligne;

        return $this;
    }

    /**
     * Remove ligne
     *
     * @param \GameBundle\Entity\Ligne $ligne
     */
    public function removeLigne(\GameBundle\Entity\Ligne $ligne)
    {
        $this->lignes->removeElement($ligne);
    }

    /**
     * Get lignes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLignes()
    {
        return $this->lignes;
    }

    /**
     * Add player
     *
     * @param \GameBundle\Entity\Player $player
     *
     * @return Grid
     */
    public function addPlayer(\GameBundle\Entity\Player $player)
    {
        $this->players[] = $player;

        return $this;
    }

    /**
     * Remove player
     *
     * @param \GameBundle\Entity\Player $player
     */
    public function removePlayer(\GameBundle\Entity\Player $player)
    {
        $this->players->removeElement($player);
    }

    /**
     * Get players
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPlayers()
    {
        return $this->players;
    }


    /**
     * Set nextPlayer
     *
     * @param \GameBundle\Entity\Player $nextPlayer
     *
     * @return Grid
     */
    public function setNextPlayer(\GameBundle\Entity\Player $nextPlayer = null)
    {
        $this->nextPlayer = $nextPlayer;

        return $this;
    }

    /**
     * Get nextPlayer
     *
     * @return \GameBundle\Entity\Player
     */
    public function getNextPlayer()
    {
        return $this->nextPlayer;
    }
}
