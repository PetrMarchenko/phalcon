<?php

namespace Shark\Library\Grid;

use Phalcon\Mvc\Model\Query\Builder;
use Phalcon\Mvc\Url;
use Shark\Library\QueryPaginator;

abstract class QueryGrid extends AbstractGrid
{
    /**
     * @var \Phalcon\Mvc\Model\Query\Builder
     */
    protected $query;

    /**
     * @var string
     */
    public $totalItemsColumn = null;

    /**
     * @return Builder
     */
    abstract public function getQueryBuilder();

    /**
     * @param $query
     */
    public function setQueryBuilder(Builder $query)
    {
        $this->query = $query;
    }

    protected function setSortOrder($sortBy, $direction)
    {
        if (!empty($sortBy)) {
            $sortBy = (array) $sortBy;

            foreach ($sortBy as $item) {
                if ($item) {
                    $config = $this->getPropertyConfig($item);
                    if (isset($config['sortable']) && !is_bool($config['sortable'])) {
                        $item = $config['sortable'];
                    }
                    $cols[] = $item . ($direction? ' '.$direction : '');
                }
            }
            $this->getQueryBuilder()->orderBy($cols);
        }
    }

    /**
     * @param $params
     * @param $searchConditions
     */
    public function setQueryParams($params, $searchConditions = array())
    {
        $this->params = $params;
        if (!empty($params['sortBy'])) {
            $this->sortBy = $params['sortBy'];
            $this->sortDir = !empty($params['sortDir'])? $params['sortDir'] : null;
        }

        $this->setSortOrder($this->sortBy, $this->sortDir);

        if (!empty($params['search'])) {
            foreach ($params['search'] as $key => $value) {
                if ($value || $value === '0') {
                    $config = $this->getPropertyConfig($key);

                    $value = $this->filterValue($value, $config);

                    if (!empty($config['number'])) {
                        $value = $this->parseNumber($value);
                    }

                    if (!empty($config['date'])) {
                        if (is_array($value)) {
                            $this->_setDateRanges($value, $key);
                        } else {
                            $timezone = \Phalcon\DI::getDefault()->getSession()->get('timezone');
                            $value = trim($value);
                            $date = \DateTime::createFromFormat('d-m-y', $value);

                            if ($date) {
                                $this->getQueryBuilder()->andWhere('DATE(' . $key . ' + justify_interval("' . $timezone . ' hour")) = "' . $date->format('Y-m-d') . '"');
                            }
                        }
                    } elseif (!empty($config['customSearch'])) {
                        $method = $config['customSearch'];
                        if (method_exists($this, $method)) {
                            $this->{$method}($value, $key);
                        }
                    } elseif (!empty($config['datetime'])) {
                        if (is_array($value)) {
                            $this->_setDateRanges($value, $key);
                        } else {
                            $timezone = \Phalcon\DI::getDefault()->getSession()->get('timezone');
                            $value = trim($value);
                            if (strpos($value, ' ')) {
                                $date = \DateTime::createFromFormat('d-m-y H:i:s', $value);
                                $datetime = $this->toDatetime($date->format('Y-m-d H:i:s'));
                                $this->getQueryBuilder()->andWhere('(' . $key . ')  = "' . $datetime . '"');
                            } else {
                                $date = \DateTime::createFromFormat('d-m-y', $value);
                                if ($date) {
                                    $date = $this->toDate($date->format('Y-m-d'));
                                    $this->getQueryBuilder()->andWhere('DATE(' . $key . ') = "' . $date . '"');
                                }
                            }
                        }
                    } else {
                        $placeholder = 'a' . uniqid();
                        if (strpos($key, '.')) {
                            if (is_numeric($value)) {
                                $this->getQueryBuilder()->andWhere($key . '="' . $value . '"');
                            } else {
                                $this->getQueryBuilder()->andWhere($key . " ILIKE :{$placeholder}:", array($placeholder => '%' . $value . '%'));

                            }
                        } else {
                            $aliases = array_keys($this->getQueryBuilder()->getFrom());
                            $having = $this->getQueryBuilder()->getHaving();
                            if (is_numeric($value)) {
                                if (!is_null($having)) {
                                    $this->getQueryBuilder()->having($key . "='" . $value . "' AND " . $having);
                                } else {
                                    $this->getQueryBuilder()->having($key . '="' . $value . '"');
                                }
                            } else {
                                if (!is_null($having)) {
                                    $this->getQueryBuilder()->having($key . " ILIKE :{$placeholder}: AND " . $having, array($placeholder => '%' . $value . '%'));
                                } else {
                                    $this->getQueryBuilder()->having($key . " ILIKE :{$placeholder}:", array($placeholder => '%' . $value . '%'));
                                }
                            }
                            $this->getQueryBuilder()->groupBy($aliases['0'] . '.id');
                        }
                    }
                }
            }
        } else {
            foreach ($searchConditions as $field => $filter) {
                if (isset($params['values'][$field])) {
                    $filter($this->getQueryBuilder(), $params['values'][$field], $params['values']);
                }
            }
        }

        if (!empty($params['filter'])) {
            foreach ($params['filter'] as $key => $value) {
                if ($value) {
                    $config = $this->getPropertyConfig($key);
                    if (!empty($config['datetime']) || !empty($config['date'])) {
                        if (is_array($value)) {
                            $this->_setDateRanges($value, $key);
                        }
                    }
                }

            }
        }
    }

    protected function _setDateRanges($value, $key)
    {
        $timezone = \Phalcon\DI::getDefault()->getSession()->get('timezone');

        if (!empty($value['from'])) {
            $from = \DateTime::createFromFormat('d-m-y', $value['from']);

            if ($from) {
                $this->getQueryBuilder()->andWhere('DATE(' . $key . ' + justify_interval("' . $timezone . ' hour")) >= "' . $from->format('Y-m-d') . '"');
            }
        }

        if (!empty($value['to'])) {
            $to = \DateTime::createFromFormat('d-m-y', $value['to']);

            if ($to) {
                $this->getQueryBuilder()->andWhere('DATE(' . $key . ' + justify_interval("' . $timezone . ' hour")) <= "' . $to->format('Y-m-d') . '"');
            }
        }
    }

    protected function getPaginatedRows()
    {
        $paginator = new QueryPaginator(array(
            "builder" => clone $this->getQueryBuilder(),
            "limit" => $this->params['pageSize'],
            "page" => $this->params['pageIndex'] + 1
        ));

        return @$paginator->getPaginate($this->totalItemsColumn);
    }

    protected function getAllRows($start = null)
    {
        if (!$this->allRows) {
            $query = $this->getQueryBuilder();
            if (null !== $start) {
                //$query->limit(GeneralSettings::getExportLimit());
                $query->limit(100);
                $query->offset($start);
            }
            $this->allRows = $query->getQuery()->execute();
        }
        return $this->allRows;
    }
}