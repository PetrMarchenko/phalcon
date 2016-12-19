<?php

namespace Shark\Library;

use Phalcon\Mvc\Model\Query\Builder;

class QueryPaginator extends \Phalcon\Paginator\Adapter\QueryBuilder
{
    /**
     * @var Builder
     */
    protected $_builder;

    /**
     * @var \Closure
     */
    protected $_hydrationFunction;

    /**
     * @param string $totalItemsColumn
     *
     * @return \stdClass
     */
    public function getPaginate($totalItemsColumn = null)
    {
        $paginate = new \stdClass();

        $this->_builder->limit($this->_limitRows, ($this->_page - 1) * $this->_limitRows);

        $paginate->items = $this->_builder->getQuery()->execute();
        $paginate->total_items = $this->getTotalItems($totalItemsColumn);

        if ($this->_hydrationFunction) {
            $hydratedItems = array();
            foreach ($paginate->items as $item) {
                $hydratedItems[] = call_user_func($this->_hydrationFunction, $item);
            }
            $paginate->items = $hydratedItems;
        }



        if ($this->_limitRows) {
            $paginate->total_pages = ceil($paginate->total_items / $this->_limitRows);
        } else {
            $paginate->total_pages = 0;
        }

        if ($this->_page < 0 || $this->_page > $paginate->total_pages) {
            $this->_page = 0;
        }
        $paginate->current = (int) $this->_page;

        return $paginate;
    }

    /**
     * @param callable|\Closure $function
     */
    public function setHydration(\Closure $function)
    {
        $this->_hydrationFunction = $function;
    }

    /**
     * @param $totalItemsColumn
     *
     * @return int
     */
    protected function getTotalItems($totalItemsColumn)
    {
        $di = \Phalcon\DI\FactoryDefault::getDefault();

        $db = $di->getShared('dbSlave');

        $sql = preg_replace('%(LIMIT|OFFSET|ORDER BY).*$%', '', $db::$lastStatement); //remove orders and limits

        $sql = preg_replace('%, "\w+"\."\w+" AS "\_\w+"%ui', '', $sql); //clear from clause


        if ($totalItemsColumn) {
            $sql = preg_replace('%^SELECT (.*) FROM%ui', 'SELECT ' . $totalItemsColumn . ' FROM', $sql);
        }

        $bindTypes = $this->_builder->getQuery()->getBindTypes();
        $bindParams =  $this->_builder->getQuery()->getBindParams();

        //unset limit and offset for phalcon v2
        unset($bindTypes['APL0'], $bindParams['APL0'], $bindTypes['APL1'], $bindParams['APL1']);

        $row = $db->fetchOne(
            'SELECT COUNT(*) total FROM (' . $sql . ') t',
            $bindTypes,
            $bindParams
        );

        return (int) $row['total'];
    }
}