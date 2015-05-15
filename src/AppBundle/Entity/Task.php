<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TaskRepository")
 * @ORM\Table(name="task")
 */
class Task {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=50) 
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=255) 
     */
    protected $description;

    /**
     * @ORM\Column(type="string", length=50) 
     */
    protected $state;

    /**
     * @ORM\Column(type="integer")
     */
    protected $estimated_time;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $spended_time;

    /**
     * @ORM\Column(type="datetime", length=50) 
     */
    protected $created_at;
    
    /**
     * @ORM\OneToMany(targetEntity="Task", mappedBy="parent")
     * */
    protected $children;

    /**
     * @ORM\ManyToOne(targetEntity="Task", inversedBy="children")
     * */
    protected $parent;

    public function __construct() {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return Task
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
     * Set description
     *
     * @param string $description
     * @return Task
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set state
     *
     * @param string $state
     * @return Task
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return string 
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set estimated_time
     *
     * @param integer $estimatedTime
     * @return Task
     */
    public function setEstimatedTime($estimatedTime)
    {
        $this->estimated_time = $estimatedTime;

        return $this;
    }

    /**
     * Get estimated_time
     *
     * @return integer 
     */
    public function getEstimatedTime()
    {
        return $this->estimated_time;
    }

    /**
     * Set spended_time
     *
     * @param integer $spendedTime
     * @return Task
     */
    public function setSpendedTime($spendedTime)
    {
        $this->spended_time = $spendedTime;

        return $this;
    }

    /**
     * Get spended_time
     *
     * @return integer 
     */
    public function getSpendedTime()
    {
        return $this->spended_time;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return Task
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;

        return $this;
    }

    /**
     * Get created_at
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Add children
     *
     * @param \AppBundle\Entity\Task $children
     * @return Task
     */
    public function addChild(\AppBundle\Entity\Task $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param \AppBundle\Entity\Task $children
     */
    public function removeChild(\AppBundle\Entity\Task $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set parent
     *
     * @param \AppBundle\Entity\Task $parent
     * @return Task
     */
    public function setParent(\AppBundle\Entity\Task $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \AppBundle\Entity\Task 
     */
    public function getParent()
    {
        return $this->parent;
    }
}
