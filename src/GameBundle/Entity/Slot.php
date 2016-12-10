<?php

namespace GameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Slot
 *
 * @ORM\Table(name="slot")
 * @ORM\Entity(repositoryClass="GameBundle\Repository\SlotRepository")
 */
class Slot
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
     * @var \Grid
     *
     * @ORM\ManyToOne(targetEntity="Player", inversedBy="slots")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="player", referencedColumnName="id")
     * })
     */
    private $player;

    /**
     * @var \Colonne
     *
     * @ORM\ManyToOne(targetEntity="Colonne", inversedBy="slots")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="colonne", referencedColumnName="id")
     * })
     */
    private $colonne;

    /**
     * @var \Colonne
     *
     * @ORM\ManyToOne(targetEntity="Ligne", inversedBy="slots")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ligne", referencedColumnName="id")
     * })
     */
    private $ligne;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * Set colonne
     *
     * @param \GameBundle\Entity\Colonne $colonne
     *
     * @return Slot
     */
    public function setColonne(\GameBundle\Entity\Colonne $colonne = null)
    {
        $this->colonne = $colonne;
        $colonne->addSlot($this);


        return $this;
    }

    /**
     * Get colonne
     *
     * @return \GameBundle\Entity\Colonne
     */
    public function getColonne()
    {
        return $this->colonne;
    }

    /**
     * Set ligne
     *
     * @param \GameBundle\Entity\Ligne $ligne
     *
     * @return Slot
     */
    public function setLigne(\GameBundle\Entity\Ligne $ligne = null)
    {
        $this->ligne = $ligne;
        $ligne->addSlot($this);

        return $this;
    }

    /**
     * Get ligne
     *
     * @return \GameBundle\Entity\Ligne
     */
    public function getLigne()
    {
        return $this->ligne;
    }

    /**
     * Set player
     *
     * @param string $player
     *
     * @return Slot
     */
    public function setPlayer($player)
    {
        $this->player = $player;

        return $this;
    }

    /**
     * Get player
     *
     * @return string
     */
    public function getPlayer()
    {
        return $this->player;
    }

    /**
     * Set slots
     *
     * @param \GameBundle\Entity\Player $slots
     *
     * @return Slot
     */
    public function setSlots(\GameBundle\Entity\Player $slots = null)
    {
        $this->slots = $slots;

        return $this;
    }

    /**
     * Get slots
     *
     * @return \GameBundle\Entity\Player
     */
    public function getSlots()
    {
        return $this->slots;
    }
}
