<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Event\PreFlushEventArgs;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TaskRepository")
 * @ORM\Table(name="task")
 * @ORM\HasLifecycleCallbacks()
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
     * @ORM\Column(type="integer", nullable=true)
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

    /**
     * @ORM\ManyToOne(targetEntity="Project", inversedBy="task")
     * */
    protected $project;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="task")
     * */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Sprint", inversedBy="task")
     */
    protected $sprint;

    /**
     * @ORM\OneToMany(targetEntity="Log", mappedBy="task")
     * */
    protected $log;

    public function __construct() {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
        $this->log = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Task
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
     * @return Task
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
     * Set state
     *
     * @param string $state
     * @return Task
     */
    public function setState($state) {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return string
     */
    public function getState() {
        return $this->state;
    }

    /**
     * Set estimated_time
     *
     * @param integer $estimatedTime
     * @return Task
     */
    public function setEstimatedTime($estimatedTime) {
        $this->estimated_time = $estimatedTime;

        return $this;
    }

    /**
     * Get estimated_time
     *
     * @return integer
     */
    public function getEstimatedTime() {
        return $this->estimated_time;
    }

    /**
     * Set spended_time
     *
     * @param integer $spendedTime
     * @return Task
     */
    public function setSpendedTime($spendedTime) {
        $this->spended_time = $spendedTime;

        return $this;
    }

    /**
     * Get spended_time
     *
     * @return integer
     */
    public function getSpendedTime() {
        return $this->spended_time;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return Task
     */
    public function setCreatedAt($createdAt) {
        $this->created_at = $createdAt;

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
     * Add children
     *
     * @param \AppBundle\Entity\Task $children
     * @return Task
     */
    public function addChild(\AppBundle\Entity\Task $children) {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param \AppBundle\Entity\Task $children
     */
    public function removeChild(\AppBundle\Entity\Task $children) {
        $this->children->removeElement($children);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren() {
        return $this->children;
    }

    /**
     * Set parent
     *
     * @param \AppBundle\Entity\Task $parent
     * @return Task
     */
    public function setParent(\AppBundle\Entity\Task $parent = null) {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \AppBundle\Entity\Task
     */
    public function getParent() {
        return $this->parent;
    }

    /**
     * Set project
     *
     * @param \AppBundle\Entity\Project $project
     * @return Task
     */
    public function setProject(\AppBundle\Entity\Project $project = null) {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return \AppBundle\Entity\Project
     */
    public function getProject() {
        return $this->project;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     * @return Task
     */
    public function setUser(\AppBundle\Entity\User $user = null) {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * Set sprint
     *
     * @param \AppBundle\Entity\Sprint $sprint
     * @return Task
     */
    public function setSprint(\AppBundle\Entity\Sprint $sprint = null) {
        $this->sprint = $sprint;

        return $this;
    }

    /**
     * Get sprint
     *
     * @return \AppBundle\Entity\Sprint 
     */
    public function getSprint() {
        return $this->sprint;
    }

    /**
     * Add log
     *
     * @param \AppBundle\Entity\Log $log
     * @return Task
     */
    public function addLog(\AppBundle\Entity\Log $log) {
        $this->log[] = $log;

        return $this;
    }

    /**
     * Remove log
     *
     * @param \AppBundle\Entity\Log $log
     */
    public function removeLog(\AppBundle\Entity\Log $log) {
        $this->log->removeElement($log);
    }

    /**
     * Get log
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getLog() {
        return $this->log;
    }

    /**
     * @ORM\PreFlush
     */
    public function setParentState(PreFlushEventArgs $args) {

        $em = $args->getEntityManager();

        if (!$this->getParent() || $this->getState() != 'Finished') {
            return null;
        }

        $parent = $this->getParent();

        while ($parent) {
            foreach ($parent->getChildren() as $anotherChild) {
                if ($anotherChild->getState() != 'Finished') {
                    break 2;
                }
            }
            $parent->setState('Finished');

            $em->persist($parent);

            if ($parent->getParent() !== null) {
                $parent = $parent->getParent();
            } else {
                break;
            }
        }
    }

}
