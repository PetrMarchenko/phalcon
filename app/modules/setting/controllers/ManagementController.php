<?php

namespace Shark\Module\Setting\Controller;

use Shark\Library\Controller\ControllerBase;
use Shark\Module\Setting\Grid\SettingManager;
use Shark\Module\Setting\Models\Setting;

/**
 * Class ManagementController
 * @RoutePrefix("/setting")
 */
class ManagementController extends ControllerBase
{

    /**
     * @Route("/", "name" = "setting_show")
     */
    public function indexAction()
    {
        \Phalcon\Tag::prependTitle('Setting management | ');
        $this->gridDefaults['sortBy'] = 'setting.key';
        $this->gridDefaults['sortDir'] = 'asc';

        $grid = new SettingManager();
        $params = $this->getGridParams();
        $grid->setQueryParams($params);


        if ($this->request->isAjax() || $this->request->get('export')) {
            return $this->paginate($grid);
        }
        $this->view->grid = $grid;
    }

    /**
     * @Route("/edit/{id}", "name" = "setting_edit")
     */
    public function editAction($id)
    {
        var_dump('edit'); exit;
    }

    /**
     * @Route("/create", "name" = "setting_create")
     */
    public function createAction()
    {
        var_dump('create'); exit;
    }
}