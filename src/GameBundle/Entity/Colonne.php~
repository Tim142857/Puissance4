<?php

namespace GameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Colonne
 *
 * @ORM\Table(name="colonne")
 * @ORM\Entity(repositoryClass="GameBundle\Repository\ColonneRepository")
 */
class Colonne
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
     * @ORM\OneToMany(targetEntity="GameBundle\Entity\Slot", mappedBy="colonne", cascade={"remove", "persist"})
     */
    private $slots;

    /**
     * @var \Grid
     *
     * @ORM\ManyToOne(targetEntity="Grid", inversedBy="colonnes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="grid", referencedColumnName="id")
     * })
     */
    private $grid;


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
     * Constructor
     */
    public function __construct()
    {
        $this->slots = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add slot
     *
     * @param \GameBundle\Entity\Slot $slot
     *
     * @return Colonne
     */
    public function addSlot(\GameBundle\Entity\Slot $slot)
    {
        $this->slots[] = $slot;

        return $this;
    }

    /**
     * Remove slot
     *
     * @param \GameBundle\Entity\Slot $slot
     */
    public function removeSlot(\GameBundle\Entity\Slot $slot)
    {
        $this->slots->removeElement($slot);
    }

    /**
     * Get slots
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSlots()
    {
        return $this->slots;
    }

    /**
     * Set grid
     *
     * @param \GameBundle\Entity\Grid $grid
     *
     * @return Colonne
     */
    public function setGrid(\GameBundle\Entity\Grid $grid = null)
    {
        $this->grid = $grid;
        $grid->addColonne($this);

        return $this;
    }

    /**
     * Get grid
     *
     * @return \GameBundle\Entity\Grid
     */
    public function getGrid()
    {
        return $this->grid;
    }
}
