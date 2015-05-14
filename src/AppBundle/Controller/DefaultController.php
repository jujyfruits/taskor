<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller {

    /**
     * @Route("/app/example", name="homepage")
     */
    public function indexAction(Request $request) {
        // is it an Ajax request?
        $isAjax = $request->isXmlHttpRequest();

        // what's the preferred language of the user?
        $language = $request->getPreferredLanguage(array('en', 'fr'));

        // get the value of a $_GET parameter
        $pageName = $request->query->get('page');

        // get the value of a $_POST parameter
        $pageName = $request->request->get('page');

        // store a message for the very next request
        $this->addFlash('notice', 'Congratulations, your action succeeded!');

        return $this->render('default/index.html.twig');
    }

    /**
     * @Route("/hole", name="hole")
     */
    public function holeAction() {
        return $this->redirectToRoute('hello', array('name' => 'Fabien'));
    }

    /**
     * @Route("/hello/{name}.{_format}",
     *  defaults={"_format"="html"},
     *  requirements = { "_format" = "html|xml|json"},
     *  name="hello"
     * )
     */
    public function helloAction($name, $_format) {
        return $this->render('default/hello.' . $_format . '.twig', array(
                    'name' => $name
        ));
    }

}
