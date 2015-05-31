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
     * @Route("/", name="project_index")
     */
    public function indexAction() {
        $em = $this->getDoctrine()->getEntityManager();
        $projects = $em->getRepository('AppBundle:Project')->findAll();
        $user = $this->container->get('security.context')->getToken()->getUser();
        $user_projects = $em->getRepository('AppBundle:Project')->getUserProjects($user->getId());
        $collection = $user->getProject();
        $project_ids = $collection->map(function($entity) {
                    return $entity->getId();
                })->toArray();
        $events = $em->getRepository('AppBundle:Log')->getLatestEventsByProjects($project_ids);

        return $this->render('project/index.html.twig', array(
                    'projects' => $projects,
                    'user_projects' => $user_projects,
                    'events' => $events
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
     * @Route("/projects/archived/", name="projects_archived")
     */
    public function archivedProjectsAction() {
        $em = $this->getDoctrine()->getEntityManager();
        $user = $this->container->get('security.context')->getToken()->getUser();
        $user_projects = $em->getRepository('AppBundle:Project')->getArchivedUserProjects($user->getId());

        return $this->render('project/archived.html.twig', array(
                    'user_projects' => $user_projects
        ));
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

        $project = $em->getRepository('AppBundle:Project')->find($id);

        if (!$project) {
            throw $this->createNotFoundException('Unable to find requested project.');
        }

        $actual_sprints = $em->getRepository('AppBundle:Sprint')->getActualSprintsTasksByProjectId($id);

        $expired_sprints = $em->getRepository('AppBundle:Sprint')->getExpiredSprintsTasksByProjectId($id);

        $unassigned_tasks = $em->getRepository('AppBundle:Task')->getUnassignedTasksByProjectId($id);

        $user = $this->container->get('security.context')->getToken()->getUser()->getUserName();

        return $this->render('project/show.html.twig', array(
                    'project' => $project,
                    'actual_sprints' => $actual_sprints,
                    'unassigned_tasks' => $unassigned_tasks,
                    'expired_sprints' => $expired_sprints,
                    'user' => $user
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
     * @Route("/project/archive", name="ajax_archive_project")
     */
    public function ajaxArchiveProjectAction(Request $request) {
        $id = $request->get('project_id');
        $em = $this->getDoctrine()->getEntityManager();
        $project = $em->getRepository('AppBundle:Project')->findOneBy(array('id' => $id));
        if (!$project) {
            throw $this->createNotFoundException('Unable to find requested project.');
        }
        if ($request->get('action') == 'unarchive') {
            $project->setArchived(null);
        } elseif ($request->get('action') == 'archive') {
            $project->setArchived(true);
        }
        $em->persist($project);
        $em->flush();
        return new Response();
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