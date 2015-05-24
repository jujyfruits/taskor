<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Project;
use AppBundle\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Form\Type\ProjectType;

class ProjectController extends Controller {

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
        $spended_time = ($new_state == 'deny') ? null : $request->get('spended_time');

        if (empty($project_id) || empty($task_id) || empty($new_state))
            return;

        $em = $this->getDoctrine()->getEntityManager();
        $task = $em->getRepository('AppBundle:Task')->getProjectTaskById($project_id, $task_id);

        $project = $em->getRepository('AppBundle:Task')->findOneBy(array('id' => $project_id));
        if (empty($task))
            return;

        $task->setEstimatedTime($estimated_time);
        $task->setSpendedTime($spended_time);

        if ($new_state == 'start') {
            $task->setState('Started');
            $task->setUser($this->container->get('security.context')->getToken()->getUser());
        } elseif ($new_state == 'finish') {
            $task->setState('Finished');
        } elseif ($new_state == 'deny') {
            $task->setState('Unstarted');
            $task->setUser(null);
        } else {
            return;
        }
        $em->persist($task);
        $em->flush();
        $view = $this->renderView('project/tasks/task_list.html.twig', array('task' => $task, 'project' => $project));
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
     * @Route("/", name="project_index")
     */
    public function indexAction() {
        $em = $this->getDoctrine()->getEntityManager();
        $projects = $em->getRepository('AppBundle:Project')->findAll();
        $user = $this->container->get('security.context')->getToken()->getUser();
        $user_projects = $user->getProject();
        return $this->render('project/index.html.twig', array(
                    'projects' => $projects,
                    'user_projects' => $user_projects
        ));
    }

    /**
     * @Route("/project/new", name="project_create")
     */
    public function createAction() {
        $project = new Project();
        $form = $this->createForm(new ProjectType(), $project);
        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
                $user = $this->container->get('security.context')->getToken()->getUser();
                $project->addUser($user);
                $em->persist($project);
                $em->flush();
                return $this->redirectToRoute('project_index');
            }
        }
        return $this->render('project/new.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/project/{id}/invite", requirements={"id" = "\d+"}, name="invite_to_project")
     */
    public function inviteToProjectAction($id) {
        $em = $this->getDoctrine()->getEntityManager();
        $project = $em->getRepository('AppBundle:Project')->findOneBy(array('id' => $id));
        if (!$project) {
            throw $this->createNotFoundException('Unable to find requested project.');
        }
        $users = $em->getRepository('AppBundle:User')->findNotParticipantsOfProject($id);
        $form = $this->createFormBuilder()
                ->add('users', 'entity', array(
                    'label' => 'Отсутствующие пользователи',
                    'class' => 'AppBundle:User',
                    'choices' => $users,
                    'property' => 'username',
                    'multiple' => true,
                    'expanded' => true
                ))
                ->getForm();
        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            $users = $form->getData()['users'];
            if ($form->isValid()) {
                foreach ($users as $user) {
                    $project->addUser($user);
                    $user->addProject($project);
                    $em->persist($project);
                    $em->persist($user);
                }
                $em->flush();
                return $this->redirectToRoute('project_index');
            }
        }
        return $this->render('project/invite.html.twig', array(
                    'form' => $form->createView(),
                    'project' => $project,
                    'id' => $id));
    }

    /**
     * @Route("/project/{id}/", requirements={"id" = "\d+"}, name="project_show")
     */
    public function showAction($id) {
        $em = $this->getDoctrine()->getEntityManager();
        $project = $em->getRepository('AppBundle:Project')->findOneBy(array('id' => $id));
        if (!$project) {
            throw $this->createNotFoundException('Unable to find requested project.');
        }
        $sprints = $project->getSprint();

        // неназначенные и просроченные 
        $all_tasks = $project->getTask();

        return $this->render('project/show.html.twig', array(
                    'project' => $project,
                    'all_tasks' => $all_tasks,
                    'sprints' => $sprints
        ));
    }

    /**
     * @Route("/project/{id}/edit", requirements={"id" = "\d+"}, name="project_edit")
     */
    public function editAction($id) {
        $em = $this->getDoctrine()->getEntityManager();
        $project = $em->getRepository('AppBundle:Project')->findOneBy(array('id' => $id));
        if (!$project) {
            throw $this->createNotFoundException('Unable to find requested project.');
        }
        $form = $this->createForm(new ProjectType(), $project);
        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $em->persist($project);
                $em->flush();
                return $this->redirectToRoute('project_index');
            }
        }
        return $this->render('project/edit.html.twig', array(
                    'project' => $project,
                    'form' => $form->createView()
        ));
    }

    /**
     * @Route("/project/{id}/archive", requirements={"id" = "\d+"}, name="project_archive")
     */
    public function archiveAction($id) {
        $em = $this->getDoctrine()->getEntityManager();
        $project = $em->getRepository('AppBundle:Project')->findOneBy(array('id' => $id));
        if (!$project) {
            throw $this->createNotFoundException('Unable to find requested project.');
        }

        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
                $project->setArchived(true);
                $em->persist($project);
                $em->flush();
                return $this->redirectToRoute('project_index');
            }
        }
        return $this->render('project/archive.html.twig', array(
                    'project' => $project,
                    'code' => $code,
                    'form' => $form->createView()
        ));
    }

}

/*  Action to add by UserName in text field
     *
     *
      public function inviteToProjectAction($id) {
      $em = $this->getDoctrine()->getEntityManager();
      $project = $em->getRepository('AppBundle:Project')->findOneBy(array('id' => $id));
      if (!$project) {
      throw $this->createNotFoundException('Unable to find requested project.');
      }

      $user = new User();
      $form = $this->createFormBuilder($user)
      ->add('username', 'text')
      ->getForm();

      if ($form->isValid()) {

      $user = $em->getRepository('AppBundle:User')->findOneBy(array('username' => $user->getUsername()));
      $project->addUser($user);
      $user->addProject($project);

      $em->persist($project);
      $em->persist($user);
      $em->flush();

      return $this->redirectToRoute('project_index');
      }
      }
      return $this->render('project/invite.html.twig', array(
      'form' => $form->createView(),
      'project' => $project,
      'id' => $id));
      }
     */