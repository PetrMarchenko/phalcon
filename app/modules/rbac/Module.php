<?php

namespace Shark\Module\Rbac;

use Phalcon\Loader;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\View;
use Phalcon\Mvc\ModuleDefinitionInterface;

class Module implements ModuleDefinitionInterface
{

    /**
     * Register a specific autoloader for the module
     */
    public function registerAutoloaders(\Phalcon\DiInterface $dependencyInjector = null)
    {
    }

    /**
     * Register specific services for the module
     */
    public function registerServices(\Phalcon\DiInterface $dependencyInjector)
    {
        //Registering the view component
        $dependencyInjector->set('view', function () {
            $view = new View();
            $view->setViewsDir(APP_DIR . '/modules/rbac/views/');
            $view->registerEngines(array(
                ".volt" => 'voltService'
            ));
            $view->setLayoutsDir('../../../views/templates/');
            $view->setLayout('base');
            return $view;
        });

    }
}