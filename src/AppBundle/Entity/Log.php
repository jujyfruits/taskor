<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\LogRepository")
 * @ORM\Table(name="log")
 * @ORM\HasLifecycleCallbacks
 */
class Log {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Task", inversedBy="log")
     * */
    protected $task;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="log")
     * */
    protected $user;

    /**
     * @ORM\Column(type="string", length=50)
     */
    protected $event;

    /**
     * @ORM\Column(type="datetime", length=50)
     */
    protected $date;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set event
     *
     * @param string $event
     * @return Log
     */
    public function setEvent($event) {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event
     *
     * @return string 
     */
    public function getEvent() {
        return $this->event;
    }

    /**
     * Set task
     *
     * @param \AppBundle\Entity\Task $task
     * @return Log
     */
    public function setTask(\AppBundle\Entity\Task $task = null) {
        $this->task = $task;

        return $this;
    }

    /**
     * Get task
     *
     * @return \AppBundle\Entity\Task 
     */
    public function getTask() {
        return $this->task;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     * @return Log
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
     * Set date
     *
     * @param \DateTime $date
     * @return Log
     */
    public function setDate($date) {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate() {
        return $this->date;
    }

    /**
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updatedTimestamps() {
        if ($this->getDate() == null) {
            $this->setDate(new \DateTime('now'));
        }
    }

}
