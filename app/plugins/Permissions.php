<?php

namespace Shark\Plugin;

use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\User\Plugin;
use Phalcon\Acl;
use Phalcon\Acl\Role;
use Phalcon\Acl\Adapter\Memory as AclList;
use Phalcon\Acl\Resource;
use Shark\Module\Rbac\Models\Resources;
use Shark\Module\User\Models\Roles;
use Shark\Module\User\Models\RolesInherited;
use Shark\Module\User\Models\Users;


class Permissions extends Plugin
{

    public function beforeExecuteRoute(Event $event, Dispatcher $dispatcher)
    {
        /**
         * @var Users $user
         */
        $user = $this->session->get('auth');


        $acl = new AclList();
        $acl->setDefaultAction(
            Acl::DENY
//            Acl::ALLOW
        );

        $roles = Roles::find();
        foreach ($roles as $role) {
            $acl->addRole($role->id);
        }

        $rolesInherits = RolesInherited::find();
        foreach ($rolesInherits as $rolesInherit) {
            $acl->addInherit($rolesInherit->roleId, $rolesInherit->parentsRoleId);
        }

        /**
         * @var \Shark\Library\AnnotationRouter $router
         */
        $router = \Phalcon\DI::getDefault()->getShared('router');
        $resources = $router->getRoutes();

        foreach ($resources as $value ) {
            $resource = $value->getPaths();
            $acl->addResource(
                new Resource($resource['module'] . '/' . $resource['controller']),
                $resource['action']
            );
        }

        $resources = Resources::find();
        foreach ($resources as $value) {
            if($acl->isResource($value->key)) {
                try{
                    $acl->allow($value->roleId, $value->key, $value->action);
                } catch (\Exception $e) {
                    $resources = Resources::findFirst([
                        'key = :key: AND action = :action:',
                        "bind" => ['key' => $value->key, 'action' => $value->action]
                    ]);
                    $resources->delete();
                    continue;
                }
            }
        }


        $module = $dispatcher->getModuleName();
        $controller = $dispatcher->getControllerName();


        $userRole = Roles::ROLE_GUESTS;
        if ($user) {
            $userRole = $user->usersRoles->roleId;
        }

        $resource = $module . '/' . $controller;
        $action = $dispatcher->getActionName();

        $view = \Phalcon\DI::getDefault()->getShared('view');
        $view->sharkUserRoleId = $userRole;
        //$view->sharkResource = $resource;

        $allowed = false;
        if($acl->isResource($resource)) {
            $allowed = $acl->isAllowed($userRole, $resource, $action);
        }

        if(!$allowed) {
            $this->flash->error("You don't have access to this module");
            $dispatcher->setNamespaceName('Shark\Module\Home\Controller');
            $dispatcher->setModuleName('home');
            $view = \Phalcon\DI::getDefault()->getShared('view');
            $view->setViewsDir(APP_DIR . '/modules/home/views/');
            $dispatcher->forward(
                array(
                    'controller' => 'index',
                    'action' => 'index',
                )
            );
            return false;
        }
    }
}
