<?php

namespace Shark\Module\Home\Controller;

use Shark\Library\Controller\ControllerBase;

/**
 * Class ErrorController
 * @RoutePrefix("/error")
 */
class ErrorController extends ControllerBase
{
    /**
     * Index action
     *
     * @Route("/", "name" = "error_default")
     */
    public function defaultAction(\Exception $exception)
    {
        $this->response->setStatusCode(500, 'Not Found');
        $this->view->error = $exception->getMessage();
    }

    /**
     * Index action
     *
     * @Route("/error", "name" = "error_error")
     */
    public function errorAction($exception)
    {
        $this->response->setStatusCode(404, 'Not Found');
        $this->view->error = $exception->getMessage();
    }
}