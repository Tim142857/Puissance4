<?php

namespace GameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Player
 *
 * @ORM\Table(name="player")
 * @ORM\Entity(repositoryClass="GameBundle\Repository\PlayerRepository")
 */
class Player
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="color", type="string", length=255)
     */
    private $color;

    /**
     * @var \Grid
     *
     * @ORM\ManyToOne(targetEntity="Grid", inversedBy="players")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="grid", referencedColumnName="id")
     * })
     */
    private $grid;

    /**
     * @ORM\OneToMany(targetEntity="GameBundle\Entity\Slot", mappedBy="player", cascade={"remove", "persist"})
     */
    private $slots;

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
     * Set name
     *
     * @param string $name
     *
     * @return Player
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set color
     *
     * @param string $color
     *
     * @return Player
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Get color
     *
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Set grid
     *
     * @param \GameBundle\Entity\Grid $grid
     *
     * @return Player
     */
    public function setGrid(\GameBundle\Entity\Grid $grid = null)
    {
        $this->grid = $grid;
        $grid->addPlayer($this);
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
     * @return Player
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
}
