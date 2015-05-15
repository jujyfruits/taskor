<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Project;
use AppBundle\Entity\User;

class ProjectController extends Controller {

    /**
     * @Route("/project/", name="project_index")
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
        /*
          CUSTOM REPOSITORY QUERIES
          $em = $this->getDoctrine()->getEntityManager();
          $users = $em->getRepository('AppBundle:Project')
          ->findOfThisUser();
         */

        $project = new Project();
        $form = $this->createFormBuilder($project)
                ->add('name', 'text')
                ->add('description', 'textarea')
                ->getForm();
        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
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

        $usernames = array();

        foreach ($users as $user) {
            $usernames[$user->getUsername()] = $user->getUsername();
        }

        $form = $this->createFormBuilder()
                ->add('usernames', 'choice', [
                    'choices' => $usernames,
                    'multiple' => true,
                    'expanded' => true
                ])
                ->getForm();

        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            $usernames = $form->getData()['usernames'];
            if ($form->isValid()) {
                foreach ($usernames as $username) {
                    $user = $em->getRepository('AppBundle:User')->findOneBy(array('username' => $username));
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
     * @Route("/project/{id}", requirements={"id" = "\d+"}, name="project_show")
     */
    public function showAction($id) {
        $em = $this->getDoctrine()->getEntityManager();
        $project = $em->getRepository('AppBundle:Project')->findOneBy(array('id' => $id));
        if (!$project) {
            throw $this->createNotFoundException('Unable to find requested project.');
        }

        return $this->render('project/show.html.twig', array('project' => $project, 'id' => $id));
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
}
