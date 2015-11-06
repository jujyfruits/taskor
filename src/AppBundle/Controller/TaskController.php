<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Project;
use AppBundle\Entity\User;
use AppBundle\Entity\Task;
use AppBundle\Entity\Sprint;
use AppBundle\Entity\Log;
use AppBundle\Helper\ObsceneCensorRus;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Form\Type\TaskType;

class TaskController extends Controller {

    /**
     * @Route("/tracker/unauthoriseduser/",
     *  name="ajax_set_new_referrer")
     */
    public function unauthoriseduser(Request $request) {
        
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }
        
        $referrer = $request->get('referrer');
        
        $referrer = substr($referrer, 0, 48);

        $em = $this->getDoctrine()->getEntityManager();
        $log = new Log;
        $log->setEvent($referrer);

        $em->persist($log);
        $em->flush();
        $response = new Response();
        return $response;
    }

    /**
     * @Route("/project/task/ajaxchangestate/",
     *  name="ajax_change_task_state")
     */
    public function ajaxChangeTaskStateAction(Request $request) {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $project_id = $request->get('project_id');
        $task_id = $request->get('task_id');
        $new_state = $request->get('new_state');
        $estimated_time = ($new_state == 'deny') ? null : $request->get('estimated_time');
        $spended_time = (($new_state == 'deny') || ($new_state == 'start')) ? null : $request->get('spended_time');
        $view_template = $request->get('view');

        if (empty($project_id) || empty($task_id) || empty($new_state))
            return;

        $em = $this->getDoctrine()->getEntityManager();
        $task = $em->getRepository('AppBundle:Task')->getProjectTaskById($project_id, $task_id);

        $project = $em->getRepository('AppBundle:Task')->findOneBy(array('id' => $project_id));
        if (empty($task))
            return;

        $task->setEstimatedTime($estimated_time);
        $task->setSpendedTime($spended_time);
        $user = $this->container->get('security.context')->getToken()->getUser();
        if ($new_state == 'start') {
            $task->setState('Started');
            $task->setUser($user);
        } elseif ($new_state == 'finish') {
            $task->setState('Finished');
        } elseif ($new_state == 'deny') {
            $task->setState('Unstarted');
            $task->setUser(null);
        } else {
            return;
        }

        $log = $this->generateLogForTask($task, $task->getState());
        $task->addLog($log);
        $em->persist($log);

        $em->persist($task);
        $em->flush();

        if ($view_template == 'list') {
            $view = $this->renderView('project/tasks/task_list.html.twig', array('task' => $task, 'project' => $project, 'user' => $user->getUsername()));
        } else {
            $view = $this->renderView('task/task_full.html.twig', array('task' => $task, 'sprint' => $task->getSprint() ? : 'Неназначено'));
        }
        $response = new Response($view);
        return $response;
    }

    /**
     * @Route("/project/task/ajaxchangestate/dialog",
     *  name="ajax_change_task_state_dialog")
     */
    public function ajaxDialogChangeTaskStateAction(Request $request) {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }
        $project_id = $request->get('project_id');
        $task_id = $request->get('task_id');
        $new_state = $request->get('new_state');

        $em = $this->getDoctrine()->getEntityManager();
        $task = $em->getRepository('AppBundle:Task')->getProjectTaskById($project_id, $task_id);
        if (empty($task))
            return;
        $form = $this->createFormBuilder($task)
                ->add('estimated_time', 'integer', array(
                    'label' => 'Предполагаемое время выполнения',
                    'disabled' => (($new_state == 'finish') || ($new_state == 'deny')),
                    'data' => ($task->getEstimatedTime()) ? $task->getEstimatedTime() : null,
                    'attr' => array('min' => 1)
                ))
                ->add('spended_time', 'integer', array(
                    'label' => 'Итоговое время выполнения',
                    'disabled' => (($new_state == 'start') || ($new_state == 'deny')),
                    'data' => ($task->getSpendedTime()) ? $task->getSpendedTime() : null,
                    'attr' => array('min' => 1)
                ))
                ->getForm();
        $view = $this->renderView('project/tasks/task_change_state_dialog.html.twig', array(
            'task' => $task,
            'new_state' => $new_state,
            'form' => $form->createView()
        ));
        $response = new Response($view);
        return $response;
    }

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
        if (!$task) {
            throw $this->createNotFoundException('Unable to find requested task.');
        }
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
        $events = $em->getRepository('AppBundle:Log')->getTaskLatestEventsByProjects($project_id, $task_id);
        //dump($events);
        $user = $this->container->get('security.context')->getToken()->getUser();

        return $this->render('task/show.html.twig', array(
                    'project' => $project,
                    'project_id' => $project_id,
                    'task' => $task,
                    'child_tasks' => $task->getChildren(),
                    'task_parents' => $task_parents,
                    'user' => $user,
                    'sprint' => $task->getSprint() ? : 'Неназначено',
                    'events' => $events
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
        list($sprints_numbers, $sprints_dates, $sprints_dates_text, $current_sprint) = $this->generateSprintsForTask($project, $task);

        $form = $this->createForm(new TaskType($parent_task, $all_tasks, $sprints_numbers, $sprints_dates_text, null), $task);
        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $sprint = $form['sprint']->getData();
                if (isset($sprint)) {
                    $sprint_number = $form['sprint']->getData();
                    $sprint = $em->getRepository('AppBundle:Sprint')->getProjectSprintByNumber($project_id, $sprint_number);
                    if (empty($sprint)) {
                        $sprint = new Sprint();
                        $sprint->setProject($project)
                                ->setNumber($sprint_number)
                                ->setDateStart($sprints_dates[$sprint_number - $current_sprint]['start_date'])
                                ->setDateEnd($sprints_dates[$sprint_number - $current_sprint]['end_date']);
                    }
                    $task->setSprint($sprint);
                    $em->persist($sprint);
                } elseif ($task->getParent()) {
                    $sprint = $task->getParent()->getSprint();
                    $task->setSprint($sprint);
                    $em->persist($sprint);
                }

                if (!ObsceneCensorRus::isAllowed($task->getName()) || !ObsceneCensorRus::isAllowed($task->getDescription())) {
                    $this->addFlash('error', "Это не те слова которые тебе нужны");
                    if (!empty($parent_task_id)) {
                        return $this->redirectToRoute('subtask_create', array(
                                    'project_id' => $project_id,
                                    'parent_task_id' => $task_id));
                    } else {
                        return $this->redirectToRoute('task_create', array(
                                    'project_id' => $project_id));
                    }
                }

                $task->setCreatedAt(new \DateTime());
                $task->setProject($project);
                $task->setState('Unstarted');

                $log = $this->generateLogForTask($task, 'Created');
                $task->addLog($log);
                $em->persist($log);

                $em->persist($task);
                $em->flush();
                $task_id = $task->getId();
                if ($form->get('saveAndAdd')->isClicked()) {
                    return $this->redirectToRoute('subtask_create', array(
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

    /**
     * @Route("/project/{project_id}/task/edit/{task_id}/", name="task_edit")
     */
    public function editAction($project_id, $task_id) {
        $em = $this->getDoctrine()->getEntityManager();
        $project = $em->getRepository('AppBundle:Project')->findOneBy(array('id' => $project_id));
        $task = $em->getRepository('AppBundle:Task')->findOneBy(array('id' => $task_id));

        $all_tasks = $project->getTask();

        list($sprints_numbers, $sprints_dates, $sprints_dates_text, $current_sprint) = $this->generateSprintsForTask($project);

        $task_sprint_number = array_search($task->getSprint(), $sprints_dates_text);

        $form = $this->createForm(new TaskType(null, $all_tasks, $sprints_numbers, $sprints_dates_text, $task_sprint_number), $task);
        $request = $this->get('request');

        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                if ($sprint_number = $form['sprint']->getData()) {
                    $sprint = $em->getRepository('AppBundle:Sprint')->getProjectSprintByNumber($project_id, $sprint_number);
                    if (empty($sprint)) {
                        $sprint = new Sprint();
                        $sprint->setProject($project)
                                ->setNumber($sprint_number)
                                ->setDateStart($sprints_dates[$sprint_number - $current_sprint]['start_date'])
                                ->setDateEnd($sprints_dates[$sprint_number - $current_sprint]['end_date']);
                    }
                    $task->setSprint($sprint);
                    $em->persist($sprint);
                }

                $log = $this->generateLogForTask($task, 'Edited');
                $task->addLog($log);
                $em->persist($log);

                $em->persist($task);
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
        return $this->render('task/edit.html.twig', array(
                    'task' => $task,
                    'form' => $form->createView(),
                    'project' => $project,
                    'users' => $project->getuser()));
    }

    public function getTaskSprint($task) {
        $sprint = $task->getSprint();
        if (!$sprint) {
            return false;
        }
        $formatter = new \IntlDateFormatter(\Locale::getDefault(), \IntlDateFormatter::NONE, \IntlDateFormatter::NONE);
        $formatter->setPattern('d MMMM Y');
        return $formatter->format($sprint->getDateStart()) . ' — ' . $formatter->format($sprint->getDateEnd());
    }

    public function generateSprintsForTask($project) {
        $sprints_numbers = array();
        $sprints_dates = array();
        $sprints_dates_text = array();
        $interval = date_diff((new \DateTime()), $project->getCreatedAt())->days;
        $current_sprint = (int) ceil($interval / $project->getSprintLength());
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
        return array($sprints_numbers, $sprints_dates, $sprints_dates_text, $current_sprint);
    }

    public function generateLogForTask($task, $event) {
        $log = new Log;
        $log->setTask($task);
        $log->setUser($this->container->get('security.context')->getToken()->getUser());
        $log->setEvent($event);
        return $log;
    }

}
