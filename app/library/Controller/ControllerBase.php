<?php

namespace Shark\Library\Controller;

use Phalcon\Mvc;
use Shark\Library\Grid\AbstractGrid;
use Shark\Library\Csv;
use Phalcon\Mvc\Url;


/**
 * Class ControllerBase
 */
class ControllerBase extends Mvc\Controller
{
    protected $gridDefaults = array(
        'pageSize' => null,
        'pageIndex' => 0,
        'search' => array()
    );

    protected function getGridParams()
    {
        $key = 'grid:' . $this->dispatcher->getModuleName() . ':' .$this->dispatcher->getControllerName()
            . ':' . $this->dispatcher->getActionName();

        $params = $this->session->get($key, array());

        if ($this->session->get('last-grid') != $key) {
            $params = array();
        }

        if ($this->request->get('sortProperty')) {
            $params['sortBy'] = $this->request->get('sortProperty');
        } elseif (empty($params['sortBy'])) {
            $params['sortBy'] = isset($this->gridDefaults['sortBy']) ? $this->gridDefaults['sortBy'] : '';
        }
        if ($this->request->get('sortDirection')) {
            $params['sortDir'] = $this->request->get('sortDirection') . ' ';
        } elseif (empty($params['sortDir'])) {
            $params['sortDir'] = isset($this->gridDefaults['sortDir']) ? $this->gridDefaults['sortDir'] : '';
        }

        if ($this->request->get('pageSize', 'int')) {
            $params['pageSize'] = $this->request->get('pageSize', 'int', 100);
        } else {
            $params['pageSize'] = 100;
        }
        $params['pageIndex'] = $this->request->get('pageIndex', 'int');

        if ($this->request->get('search')) {
            $params['search'] = $this->request->get('search');
        }
        if ($this->request->get('filter')) {
            $params['filter'] = $this->request->get('filter');
        }
        if (empty($params['pageIndex']) || $params['pageIndex'] < 1) {
            $params['pageIndex'] = 0;
        }

        $this->session->set($key, $params);
        $this->session->set('last-grid', $key);

        return $params;
    }

    protected function paginate(AbstractGrid $grid)
    {
        $this->view->disable();

        if ($this->request->get('export')) {
            $start = $this->request->get('start');

            $this->toCsv($grid->toCsv($start), $grid->getName());
        } else {
            $this->response->setJsonContent($grid->toArray());
        }
        return $this->response;
    }

    public function toCsv($data, $filename)
    {
        if (!strrpos($filename, '.csv')) {
            $filename .= '.csv';
        }

        if (is_array($data)) {
            $data = Csv::toCsv($data);
        }
        $this->response->setContentType('text/csv');
        $this->response->setHeader('Content-Disposition', 'attachment;filename=' . $filename);
        $this->response->setContent($data);

        return $this->response;
    }

}