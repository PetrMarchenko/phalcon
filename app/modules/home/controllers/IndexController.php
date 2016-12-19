<?php

namespace Shark\Module\Home\Controller;

use Shark\Library\Controller\ControllerBase;

/**
 * Class IndexController
 */
class IndexController extends ControllerBase
{
    /**
     * Index action
     *
     * @Route("/home", "name" = "home")
     */
    public function indexAction()
    {
        $this->view->name = "Home";
    }

    /**
     * About action
     *
     * @Route("/about", name="about")
     */
    public function aboutAction()
    {

        $this->view->name = "About";
    }

    /**
     * @Route("/contact", "name" = "contact")
     */
    public function contactAction()
    {
        $this->view->name = "Contact";
    }
}