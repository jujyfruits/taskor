<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProjectRepository")
 * @ORM\Table(name="project")
 */
class Project {

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
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $archived;

    /**
     * @ORM\Column(type="integer")
     */
    protected $sprint_length;

    /**
     * @ORM\Column(type="datetime", length=50)
     */
    protected $created_at;

    /**
     * @ORM\ManyToMany(targetEntity="User", inversedBy="project")
     * @ORM\JoinTable(name="users_projects")
     **/
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="Task", mappedBy="project")
     **/
    private $task;

    /**
     * @ORM\OneToMany(targetEntity="Sprint", mappedBy="project")
     */
    private $sprint;


    public function __construct() {
        $this->created_at = new \DateTime();
        $this->user = new \Doctrine\Common\Collections\ArrayCollection();
        $this->task = new \Doctrine\Common\Collections\ArrayCollection();
        $this->sprint = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Project
     */
    public function setName($name) {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Project
     */
    public function setDescription($description) {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return Project
     */
    public function setCreatedAt($created_at) {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * Get created_at
     *
     * @return \DateTime
     */
    public function getCreatedAt() {
        return $this->created_at;
    }


    /**
     * Add user
     *
     * @param \AppBundle\Entity\User $user
     * @return Project
     */
    public function addUser(\AppBundle\Entity\User $user)
    {
        $this->user[] = $user;

        return $this;
    }

    /**
     * Remove user
     *
     * @param \AppBundle\Entity\User $user
     */
    public function removeUser(\AppBundle\Entity\User $user)
    {
        $this->user->removeElement($user);
    }

    /**
     * Get user
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add task
     *
     * @param \AppBundle\Entity\Task $task
     * @return Project
     */
    public function addTask(\AppBundle\Entity\Task $task)
    {
        $this->task[] = $task;

        return $this;
    }

    /**
     * Remove task
     *
     * @param \AppBundle\Entity\Task $task
     */
    public function removeTask(\AppBundle\Entity\Task $task)
    {
        $this->task->removeElement($task);
    }

    /**
     * Get task
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * Add sprint
     *
     * @param \AppBundle\Entity\Sprint $sprint
     * @return Project
     */
    public function addSprint(\AppBundle\Entity\Sprint $sprint)
    {
        $this->sprint[] = $sprint;

        return $this;
    }

    /**
     * Remove sprint
     *
     * @param \AppBundle\Entity\Sprint $sprint
     */
    public function removeSprint(\AppBundle\Entity\Sprint $sprint)
    {
        $this->sprint->removeElement($sprint);
    }

    /**
     * Get sprint
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSprint()
    {
        return $this->sprint;
    }

    /**
     * Set archived
     *
     * @param boolean $archived
     * @return Project
     */
    public function setArchived($archived)
    {
        $this->archived = $archived;

        return $this;
    }

    /**
     * Get archived
     *
     * @return boolean 
     */
    public function getArchived()
    {
        return $this->archived;
    }

    /**
     * Set sprint_length
     *
     * @param integer $sprintLength
     * @return Project
     */
    public function setSprintLength($sprintLength)
    {
        $this->sprint_length = $sprintLength;

        return $this;
    }

    /**
     * Get sprint_length
     *
     * @return integer 
     */
    public function getSprintLength()
    {
        return $this->sprint_length;
    }
}
