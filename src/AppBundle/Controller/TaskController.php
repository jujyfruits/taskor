<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Project;
use AppBundle\Entity\User;
use AppBundle\Entity\Task;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Form\Type\TaskType;

class TaskController extends Controller {

    /**
     * @Route("/project/task/new/ajaxform", name="ajax_add_task_form")
     */
    public function addFormAction() {
        return new Response();
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

        return $this->render('task/show.html.twig', array(
                    'project' => $project,
                    'project_id' => $project_id,
                    'task' => $task,
                    'child_tasks' => $task->getChildren(),
          ));
    }

    /**
     * @Route("/project/{project_id}/task/new/", name="task_create",  defaults={"parent_task_id" = null})
     * @Route("/project/{project_id}/task/{parent_task_id}/new/")
     */
    public function createAction($project_id, $parent_task_id) {
        $em = $this->getDoctrine()->getEntityManager();
        $parent_task = null;
        if (!empty($parent_task_id)) {
            $parent_task = $em->getRepository('AppBundle:Task')->findBy(array('id' => $parent_task_id));
        }
        $project = $em->getRepository('AppBundle:Project')->findOneBy(array('id' => $project_id));
        if (!$project) {
            throw $this->createNotFoundException('Unable to find requested project.');
        }
        $task_arr = $project->getTask();
        $task = new Task();
        $form = $this->createForm(new TaskType($parent_task, $task_arr), $task);

        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
                $task->setCreatedAt(new \DateTime());
                $task->setProject($project);
                $task->setState('Unstarted');
                $em->persist($task);
                $em->flush();
                $task_id = $task->getId();
                if ($form->get('saveAndAdd')->isClicked()) {
                      return $this->redirectToRoute('task_create', array(
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
                    'project' => $project));
    }

}
