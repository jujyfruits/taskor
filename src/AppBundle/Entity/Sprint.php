<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Sprint
 *
 * @ORM\Table(name="sprint")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SprintRepository")
 */
class Sprint {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="number", type="integer")
     */
    private $number;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_start", type="date")
     */
    private $dateStart;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_end", type="date")
     */
    private $dateEnd;

    /**
     * @ORM\ManyToOne(targetEntity="Project", inversedBy="sprint")
     */
    private $project;

    /**
     * @ORM\OneToMany(targetEntity="Task", mappedBy="sprint")
     */
    private $task;

    public function __construct() {
        $this->task = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set number
     *
     * @param integer $number
     * @return Sprint
     */
    public function setNumber($number) {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return integer
     */
    public function getNumber() {
        return $this->number;
    }

    /**
     * Set dateStart
     *
     * @param \DateTime $dateStart
     * @return Sprint
     */
    public function setDateStart($dateStart) {
        $this->dateStart = $dateStart;

        return $this;
    }

    /**
     * Get dateStart
     *
     * @return \DateTime
     */
    public function getDateStart() {
        return $this->dateStart;
    }

    /**
     * Set dateEnd
     *
     * @param \DateTime $dateEnd
     * @return Sprint
     */
    public function setDateEnd($dateEnd) {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    /**
     * Get dateEnd
     *
     * @return \DateTime
     */
    public function getDateEnd() {
        return $this->dateEnd;
    }

    /**
     * Set project
     *
     * @param \AppBundle\Entity\Project $project
     * @return Sprint
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
     * Add task
     *
     * @param \AppBundle\Entity\Task $task
     * @return Sprint
     */
    public function addTask(\AppBundle\Entity\Task $task) {
        $this->task[] = $task;

        return $this;
    }

    /**
     * Remove task
     *
     * @param \AppBundle\Entity\Task $task
     */
    public function removeTask(\AppBundle\Entity\Task $task) {
        $this->task->removeElement($task);
    }

    /**
     * Get task
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTask() {
        return $this->task;
    }

    public function __toString() {
        $formatter = new \IntlDateFormatter(\Locale::getDefault(), \IntlDateFormatter::NONE, \IntlDateFormatter::NONE);
        $formatter->setPattern('d MMMM Y');
        return $formatter->format($this->getDateStart()) . ' — ' . $formatter->format($this->getDateEnd());
    }

}
