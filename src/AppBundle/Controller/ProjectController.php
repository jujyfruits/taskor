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
        $em = $this->getDoctrine()->getManager();
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
                $em = $this->getDoctrine()->getManager();
                $user = $this->container->get('security.context')->getToken()->getUser();
                $project->addUser($user);
                $user->addProject($project);
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
        $em = $this->getDoctrine()->getManager();
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
        $em = $this->getDoctrine()->getManager();
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
        $em = $this->getDoctrine()->getManager();

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
     * @Route("/project/{id}/done", requirements={"id" = "\d+"}, name="project_show_done")
     */
    public function showDoneAction($id) {
        $em = $this->getDoctrine()->getManager();
        $project = $em->getRepository('AppBundle:Project')->find($id);
        if (!$project) {
            throw $this->createNotFoundException('Unable to find requested project.');
        }
        $expired_sprint_done_tasks = $em->getRepository('AppBundle:Sprint')->getExpiredSprintsDoneTasksByProjectId($id);
        $actual_sprint_done_tasks = $em->getRepository('AppBundle:Sprint')->getActualSprintsDoneTasksByProjectId($id);
        $unassigned_done_tasks = $em->getRepository('AppBundle:Task')->getUnassignedDoneTasksByProjectId($id);
        $user = $this->container->get('security.context')->getToken()->getUser()->getUserName();
        return $this->render('project/show_done.html.twig', array(
                    'project' => $project,
                    'unassigned_done_tasks' => $unassigned_done_tasks,
                    'expired_sprint_done_tasks' => $expired_sprint_done_tasks,
                    'actual_sprint_done_tasks' => $actual_sprint_done_tasks,
                    'user' => $user
        ));
    }

    /**
     * @Route("/project/{id}/statistics", requirements={"id" = "\d+"}, name="project_statistic")
     */
    public function statisticsAction($id) {
        $em = $this->getDoctrine()->getManager();

        $project = $em->getRepository('AppBundle:Project')->find($id);

        if (!$project) {
            throw $this->createNotFoundException('Unable to find requested project.');
        }

        $stat_task_est_spend = $em->getRepository('AppBundle:Task')->getStatTasksTimeByProjectId($id);


        /*         * ******************************************************************* */
        $task_estimated_data = array();
        $task_spended_data = array();
        $task_difference_data = array();
        $arr_tasks_categories = array();

        foreach ($stat_task_est_spend as $task) {
            array_push($arr_tasks_categories, $task->getName());
            array_push($task_estimated_data, $task->getEstimatedTime());
            array_push($task_spended_data, $task->getSpendedTime());
            array_push($task_difference_data, $task->getSpendedTime() - $task->getEstimatedTime());
        }
        /*         * ******************************************************************* */

        /*         * ******************************************************************* */
        $users_busyness = $em->getRepository('AppBundle:User')->getUsersBusynessByProjectId($id);

        foreach ($users_busyness as $key => $row) {
            if ($row['sprint_id'] == null) {
                $users_busyness[$key]['sprint_id'] = 'Unassigned';
            }
        }

        $arr_data = array();
        $arr_sprints = ['Неназначенные'];
        $us_list = array();

        foreach ($users_busyness as $key => $row) {
            if (!array_key_exists($row['username'], $arr_data)) {
                $arr_data[$row['username']] = array();
            }
            if ($row['sprint_id'] == 'Unassigned') {
                array_push($arr_data[$row['username']], (int) $row['estimtime']);
            } else {
                if (!in_array($row['username'], $us_list)) {
                    array_push($arr_data[$row['username']], 0);
                    array_push($us_list, $row['username']);
                }
            }
        }
        $us_list = array();
        foreach ($project->getSprint() as $sprint) {
            array_push($arr_sprints, (string) $sprint);
            foreach ($users_busyness as $key => $row) {
                if ($row['sprint_id'] == $sprint->getId()) {
                    array_push($arr_data[$row['username']], (int) $row['estimtime']);
                    if (!in_array($row['username'], $us_list)) {
                        unset($users_busyness[$key]);
                        array_push($us_list, $row['username']);
                    }
                } else {
                    array_push($arr_data[$row['username']], 0);
                }
            }
        }

        $user_time_data = array();
        for ($i = 0; $i < count($arr_sprints); $i++) {
            $user_time_data[$i]['name'] = $arr_sprints[$i];

            if (!array_key_exists('data', $user_time_data[$i])) {
                $user_time_data[$i]['data'] = array();
            }
            foreach ($arr_data as $user) {
                if (isset($user[$i])) {
                    array_push($user_time_data[$i]['data'], $user[$i]);
                } else {

                    array_push($user_time_data[$i]['data'], 0);
                }
            }
        }
        $user_time_names = array();

        foreach ($arr_data as $key => $user) {
            array_push($user_time_names, $key);
        }
        /*         * ******************************************************************* */

        //$exp_done = $em->getRepository('AppBundle:Task')->getStatTasksExpiredByProjectId($id);
        //dump($exp_done);

        /*         * ******************************************************************* */

        $actual_tasks = $em->getRepository('AppBundle:Sprint')->getActualSprintsTasksByProjectId($id);
        $expired_tasks = $em->getRepository('AppBundle:Sprint')->getExpiredSprintsTasksByProjectId($id);
        $done_tasks = $em->getRepository('AppBundle:Sprint')->getStatSprintsDoneTasksByProjectId($id);

        $act_task_data = array();
        $exp_task_data = array();
        $act_exp_sprints = array();


        $act_exp_task_data['expired'] = array();
        foreach ($project->getSprint() as $sprint) {
            array_push($act_exp_sprints, (string) $sprint);
            foreach ($actual_tasks as $actual_sprint) {
                if ($actual_sprint->getId() == $sprint->getId()) {
                    array_push($act_exp_task_data['expired'], count($actual_sprint->getTask()));
                    unset($actual_sprint);
                } else {
                    array_push($act_exp_task_data['expired'], 0);
                }
            }
        }

        $act_exp_task_data['actual'] = array();
        foreach ($project->getSprint() as $sprint) {
            foreach ($expired_tasks as $actual_sprint) {
                if ($actual_sprint->getId() == $sprint->getId()) {
                    array_push($act_exp_task_data['actual'], count($actual_sprint->getTask()));
                    unset($actual_sprint);
                } else {
                    array_push($act_exp_task_data['actual'], 0);
                }
            }
        }


        $act_exp_task_data['done'] = array();
        foreach ($project->getSprint() as $sprint) {
            foreach ($done_tasks as $key => $row) {
                if ($row['sprint_id'] == $sprint->getId()) {
                    array_push($act_exp_task_data['done'], (int) $row['count_tasks']);
                    unset($done_tasks[$key]);
                } else {
                    array_push($act_exp_task_data['done'], 0);
                }
            }
        }


        $actual_expire_task_data[0] = [
            'name' => 'Просроченные',
            'data' => $act_exp_task_data['expired']
        ];

        $actual_expire_task_data[1] = [
            'name' => 'Актуальные',
            'data' => $act_exp_task_data['actual']
        ];

        $actual_expire_task_data[2] = [
            'name' => 'Завершенные',
            'data' => $act_exp_task_data['done']
        ];
        /*         * ******************************************************************* */

        //dump($actual_expire_task_data);
        //dump($act_exp_sprints);

        return $this->render('project/statistics.html.twig', array(
                    'project' => $project,
                    'arr_tasks_categories' => $arr_tasks_categories,
                    'task_estimated_data' => $task_estimated_data,
                    'task_spended_data' => $task_spended_data,
                    'task_difference_data' => $task_difference_data,
                    'user_time_data' => $user_time_data,
                    'user_time_names' => $user_time_names,
                    'actual_expire_task_data' => $actual_expire_task_data,
                    'act_exp_sprints' => $act_exp_sprints,
        ));
    }

    /**
     * @Route("/project/{id}/edit", requirements={"id" = "\d+"}, name="project_edit")
     */
    public function editAction($id) {
        $em = $this->getDoctrine()->getManager();
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
        $em = $this->getDoctrine()->getManager();
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
      $em = $this->getDoctrine()->getManager();
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