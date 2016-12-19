<?php

namespace Shark\Module\Rbac\Controller;

use Shark\Library\Controller\ControllerBase;
use Shark\Module\User\Models\Roles;
use Shark\Module\User\Models\Users;
use Shark\Module\Rbac\Models\Resources;

/**
 * Class IndexController
 *  @RoutePrefix("/rbac")
 */
class IndexController extends ControllerBase
{
    /**
     * Index action
     *
     * @Route("/", "name" = "rbac_show")
     */
    public function indexAction()
    {
        $roles = Roles::find();
        $table = [];

        /**
         * @var \Shark\Library\AnnotationRouter $router
         */
        $router = \Phalcon\DI::getDefault()->getShared('router');
        foreach ($router->getRoutes() as $route) {
            $row = [];
            $paths = $route->getPaths();
            $resourcesKey = $paths['module'] . '/' . $paths['controller'];
            $action = $paths['action'];

            $row['resources'] = [];
            $row['resources']['id'] = $route->getRouteId();
            $row['resources']['name'] = $resourcesKey;
            $row['resources']['action'] = $action;
            $row['resources']['url'] = $route->getPattern();
            $row['resources']['roles'] = [];
            foreach ($roles as $role) {
                $value = [];
                $isAllow = Resources::findFirst([
                    "key = :key: AND roleId = :roleId: AND action = :action:",
                    "bind" => [
                        'key' => $resourcesKey,
                        'roleId' => $role->id,
                        'action' => $action
                    ]
                ]);

                $value['role'] = $role;
                $value['isAllow'] = $isAllow;
                $row['resources']['roles'][] = $value;
            }

            $table[] = $row;
        }

        $this->view->roles = $roles;
        $this->view->table = $table;
    }

    /**
     * Resources action
     *
     * @Route("/save", "name" = "rbac_save")
     */
    public function saveAction()
    {
        $isChecked = $this->request->getPost('isChecked');
        $roleId = $this->request->getPost('roleId');
        $resourcesKey = $this->request->getPost('resourcesKey');
        $action = $this->request->getPost('action');

        if ($isChecked) {
            $resources = new Resources();
            $resources->roleId = $roleId;
            $resources->action = $action;
            $resources->key = $resourcesKey;
            $resources->save();
        } else {
            $resources = Resources::findFirst([
                "key = :key: AND roleId = :roleId: AND action = :action:",
                "bind" => [
                    'key' => $resourcesKey,
                    'roleId' => $roleId,
                    'action' => $action
                ]
            ]);
            $resources->delete();
        }

        return $this->response->setJsonContent([
            'status' => 1
        ]);
    }
}