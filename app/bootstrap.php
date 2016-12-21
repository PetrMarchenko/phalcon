<?php

namespace Shark;

use Phalcon\Events\Event;

define('APP_DIR', __DIR__);
define('LOGS_DIR', APP_DIR . '/logs/' . date('Y_m_d_') . PHP_SAPI);

if (!is_dir(LOGS_DIR)) {
    mkdir(LOGS_DIR, 0777, true);
}

require APP_DIR . '/../vendor/autoload.php';

class Application extends \Phalcon\Mvc\Application
{
    private $modules = [
        'home',
        'rbac',
        'user',
        'mailTemplate'
    ];

    public function registerAutoloaders()
    {

        $namespaces = [
            'Shark\Library'                        => APP_DIR . '/library/',
            'Shark\Plugin'                         => APP_DIR . '/plugins/',
        ];
        foreach ($this->modules as $moduleName) {
            $moduleClassName = 'Shark\Module\\' . ucfirst($moduleName);

            $namespaces[$moduleClassName . '\Controller'] = APP_DIR . '/modules/' . $moduleName . '/controllers/';
            $namespaces[$moduleClassName . '\Models'] = APP_DIR . '/modules/' . $moduleName . '/models/';
            $namespaces[$moduleClassName . '\Grid'] = APP_DIR . '/modules/' . $moduleName . '/grid/';
            $namespaces[$moduleClassName . '\Forms'] = APP_DIR . '/modules/' . $moduleName . '/forms/';
        }

        $loader = new \Phalcon\Loader();
        $loader->registerNamespaces($namespaces);
        $loader->register();
    }

    public function registerWebServices()
    {
        $this->initDefaults();
        $di = $this->di;
        $modules = $this->modules;

        $di->set('router', function () use ($modules) {
            $router = new \Shark\Library\AnnotationRouter(false);
            $router->setDefaultModule("home");

            foreach ($modules as $moduleName) {
                $controllerPath = APP_DIR . '/modules/' . $moduleName . '/controllers/';

                $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($controllerPath));
                $regex = new \RegexIterator($iterator, '/^.+Controller\.php$/i', \RecursiveRegexIterator::GET_MATCH);

                foreach ($regex as $file) {
                    $class = str_replace($controllerPath, 'Shark\\Module\\' . ucfirst($moduleName) . '\\Controller\\', $file[0]);
                    $class = str_replace('/', '\\', $class);
                    $class = str_replace('Controller.php', '', $class);

                    $router->addModuleResource($moduleName, $class);
                }
            }

            $router->removeExtraSlashes(true);
            return $router;
        });

        //Registering a dispatcher
        $di->set('dispatcher', function () use ($di) {
            $dispatcher = new \Phalcon\Mvc\Dispatcher();
            $dispatcher->setDefaultNamespace("Shark\\Module\\Home\\Controller");

            $timezone = new \Shark\Plugin\Timezone();
            $permissions = new \Shark\Plugin\Permissions();

            $eventsManager = $di->getShared('eventsManager');

            $eventsManager->attach(
                "dispatch:beforeException",
                function($event, $dispatcher, $exception) use ($di)
                {
                    $dispatcher->setNamespaceName('Shark\Module\Home\Controller');
                    $dispatcher->setModuleName('home');
                    $view = $di->getShared('view');
                    $view->setViewsDir(APP_DIR . '/modules/home/views/');

                    switch ($exception->getCode()) {
                        case \Phalcon\Mvc\Dispatcher::EXCEPTION_ACTION_NOT_FOUND:
                        case \Phalcon\Mvc\Dispatcher::EXCEPTION_HANDLER_NOT_FOUND:
                            $dispatcher->forward(
                                array(
                                    'module' => 'home',
                                    'controller' => 'error',
                                    'action' => 'error',
                                    'params' => ['exception' => $exception]
                                )
                            );
                            return false;
                            break;
                        case \Phalcon\Mvc\Dispatcher::EXCEPTION_NO_DI:
                        default:
                            $dispatcher->forward(
                                array(
                                    'module' => 'home',
                                    'controller' => 'error',
                                    'action' => 'default',
                                    'params' => ['exception' => $exception]
                                )
                            );
                            return false;
                            break;
                    }
                }
            );

            $eventsManager->attach('dispatch', $permissions);
            $eventsManager->attach('dispatch', $timezone);
            $dispatcher->setEventsManager($eventsManager);

            return $dispatcher;
        });

        //Registering a Http\Request
        $di->set('request', function () {
            return new \Phalcon\Http\Request();
        });

        //Set up the flash service
        $di->set('flash', function () {
            return new \Shark\Plugin\Flash();
        });

        $di->set('response', function () {
            return new \Phalcon\Http\Response();
        });


        $registerModules= [];
        foreach ($modules as $moduleName) {
            $registerModules["$moduleName"] = [];
            $registerModules["$moduleName"]['className'] = 'Shark\Module\\' .ucfirst($moduleName) . '\\Module';
            $registerModules["$moduleName"]['path'] = APP_DIR . '/modules/' . $moduleName .'/Module.php';
        }
        $this->registerModules($registerModules);

        $di->set('voltService', function ($view, $di) {

            /**
             * Set const.
             */
            $view->ROLE_GUESTS = 1;
            $view->ROLE_USERS = 2;
            $view->ROLE_ADMIN = 3;


            $volt = new \Phalcon\Mvc\View\Engine\Volt($view, $di);

            $volt->setOptions(array(
                "compiledPath" => APP_DIR . '/cache/',
                "compiledExtension" => ".compiled",
                "stat" => true,
                "compileAlways" => true,
            ));

            $volt->getCompiler()->addFilter('number', function ($value) {
                return 'number_format(' . $value .', 2)';
            });
            $volt->getCompiler()->addFilter('rate', function ($value) {
                return 'number_format(' . $value .', 4)';
            });
            $volt->getCompiler()->addFunction('data_path', function () {
                return '$this->getDI()->getShared("dispatcher")->getModuleName() . "/" .$this->getView()->getControllerName() . "/" . $this->getView()->getActionName()';
            });
            $volt->getCompiler()->addFunction('number_or_dash', function ($args) {
                return '(0.00 == ' . $args . ') ? "-" : ' . $args;
            });

            $volt->getCompiler()->addFunction('isActive', function ($value) use ($di)  {
                $resource = $di->getShared("dispatcher")->getModuleName() . "/"
                    . $di->getShared("dispatcher")->getControllerName() . "/"
                    . $di->getShared("dispatcher")->getActionName();
                return ($resource == substr($value, 1, -1)) ? 'active' : "false";
            });

            return $volt;
        });
    }

    public function registerCliServices()
    {
        $this->initDefaults();
        $di = $this->di;

        // Router
        $di->set('router', function () {
            return new \Phalcon\CLI\Router();
        });

    }

    public function initDefaults()
    {
        $di = $this->di;
        $this->registerAutoloaders();

        $di->setShared('dbMaster', $master = function () use ($di) {
            $config = $di->getShared('config');
            $connection = new \Shark\Library\Adapter\Mysql(array(
                'host' => $config->db->master->host,
                'username' => $config->db->master->username,
                'password' => $config->db->master->password,
                'dbname' => $config->db->master->dbname,
                'port' => $config->db->master->port,
                "options" => [
                    \PDO::ATTR_PERSISTENT => (bool) $config->db->master->persistent,
                ],
            ));
            $connection->query('set names \'utf8\'');
            return $connection;
        });

        $di->setShared('dbSlave', function () use ($di) {
            $config = $di->getShared('config');
            $connection = new \Shark\Library\Adapter\Mysql(array(
                'host' => $config->db->slave->host,
                'username' => $config->db->slave->username,
                'password' => $config->db->slave->password,
                'dbname' => $config->db->slave->dbname,
                'port' => $config->db->slave->port,
                "options" => [
                    \PDO::ATTR_PERSISTENT => (bool) $config->db->slave->persistent,
                ],
            ));
            $connection->query('set names \'utf8\'');
            return $connection;
        });

        $di->setShared('session', function () use ($di) {
            $config = $di->getShared('config');

            $session = new \Phalcon\Session\Adapter\Files(array(
                'uniqueId' => $config->db->master->dbname,
                'prefix' => $config->db->master->dbname,
                'lifetime' => 3 * 3600,
            ));
            $session->start();
            return $session;
        });

        $di->setShared('mailer', function () use ($di) {
            $config = $di->getShared('config');

            $transport = \Swift_SmtpTransport::newInstance(
                $config->mail->host,
                $config->mail->port,
                $config->mail->security
            );
            $transport->setUsername($config->mail->username);
            $transport->setPassword($config->mail->password);

            return new \Swift_Mailer($transport);
        });
    }
}