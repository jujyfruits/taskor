<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Project;
use AppBundle\Entity\User;
use AppBundle\Entity\Task;
use AppBundle\Entity\Sprint;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Form\Type\TaskType;

class TaskController extends Controller {

    /**
     * @Route("/project/{project_id}/task/{task_id}", name="task_show")
     */
    public function showAction($project_id, $task_id) {
        $em = $this->getDoctrine()->getEntityManager();
        $project = $em->getRepository('AppBundle:Project')->findOneBy(array('id' => $project_id));
        if (!$project) {
            throw $this->createNotFoundException('Unable to find requested project.');
        }
        $task = $em->getRepository('AppBundle:Task')->findOneBy(array('id' => $task_id));
        $task_parents = array();
        array_push($task_parents, $task);
        while (!isset($i)) {
            $parent = end($task_parents)->getParent();
            if (empty($parent)) {
                $i = true;
                $task_parents = array_reverse($task_parents);
                array_pop($task_parents);
            } else {
                array_push($task_parents, $parent);
            }
        }

        $user = $this->container->get('security.context')->getToken()->getUser();

        /*
          $task->setUser($user);
          $user->addTask($task);
          $em->persist($task);
          $em->persist($user);
          $em->flush();
         */
        $username = ($task->getUser()) ? $task->getUser()->getUsername() : null;

        $start_date = $task->getSprint()->getDateStart();
        $end_date = $task->getSprint()->getDateEnd();
        $formatter = new \IntlDateFormatter(\Locale::getDefault(), \IntlDateFormatter::NONE, \IntlDateFormatter::NONE);
        $formatter->setPattern('d MMMM Y');
        $sprint = $formatter->format($start_date) . ' — ' . $formatter->format($end_date);


        return $this->render('task/show.html.twig', array(
                    'project' => $project,
                    'project_id' => $project_id,
                    'task' => $task,
                    'child_tasks' => $task->getChildren(),
                    'task_parents' => $task_parents,
                    'username' => $username,
                    'sprint' => $sprint,
        ));
    }

    /**
     * @Route("/project/{project_id}/task/new/", name="task_create",  defaults={"parent_task_id" = null})
     * @Route("/project/{project_id}/task/{parent_task_id}/new/", name="subtask_create")
     */
    public function createAction($project_id, $parent_task_id) {
        $em = $this->getDoctrine()->getEntityManager();
        if (!empty($parent_task_id)) {
            $parent_task = $em->getRepository('AppBundle:Task')->findBy(array('id' => $parent_task_id));
        } else {
            $parent_task = null;
        }
        $project = $em->getRepository('AppBundle:Project')->findOneBy(array('id' => $project_id));
        if (!$project) {
            throw $this->createNotFoundException('Unable to find requested project.');
        }
        $all_tasks = $project->getTask();
        $task = new Task();

        $interval = date_diff((new \DateTime()), $project->getCreatedAt())->days;
        $current_sprint = (int) ceil($interval / $project->getSprintLength());
        $sprints_numbers = array();
        $sprints_dates = array();
        $sprints_dates_text = array();
        $formatter = new \IntlDateFormatter(\Locale::getDefault(), \IntlDateFormatter::NONE, \IntlDateFormatter::NONE);
        $formatter->setPattern('d MMMM Y');
        for ($i = 0; $i < 6; $i++) {
            $sprints_numbers[$i] = $current_sprint + $i;
            $start_date = clone $project->getCreatedAt();
            $start_date->add(date_interval_create_from_date_string(( $project->getSprintLength() * ($current_sprint + $i - 1)) . ' days'));
            $end_date = clone $start_date;
            $end_date->add(date_interval_create_from_date_string($project->getSprintLength() - 1 . 'days'));
            $sprints_dates[$i] = ['start_date' => $start_date, 'end_date' => $end_date];
            $sprints_dates_text[$i] = $formatter->format($start_date) . ' — ' . $formatter->format($end_date);
        }

        $form = $this->createForm(new TaskType($parent_task, $all_tasks, $sprints_numbers, $sprints_dates_text), $task);
        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $sprint_number = $form['sprint']->getData();
                $sprint = $em->getRepository('AppBundle:Sprint')->getProjectSprintByNumber($project_id, $sprint_number);
                if (empty($sprint)) {
                    $sprint = new Sprint();
                    $sprint->setProject($project);
                    $sprint->setNumber($sprint_number);
                    $sprint->setDateStart($sprints_dates[$sprint_number - $current_sprint]['start_date']);
                    $sprint->setDateEnd($sprints_dates[$sprint_number - $current_sprint]['end_date']);
                }
                $task->setCreatedAt(new \DateTime());
                $task->setProject($project);
                $task->setSprint($sprint);
                $task->setState('Unstarted');
                $em->persist($task);
                $em->persist($sprint);
                $em->flush();
                $task_id = $task->getId();
                if ($form->get('saveAndAdd')->isClicked()) {
                    return $this->redirectToRoute('task_create_child', array(
                                'project_id' => $project_id,
                                'parent_task_id' => $task_id));
                } else {
                    return $this->redirectToRoute('task_show', array(
                                'project_id' => $project_id,
                                'task_id' => $task_id));
                }
            }
        }
        return $this->render('task/new.html.twig', array(
                    'form' => $form->createView(),
                    'project' => $project,
                    'users' => $project->getuser()));
    }

}
