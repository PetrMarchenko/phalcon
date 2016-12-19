<?php

namespace Shark\Library\Adapter;

class Pgsql extends \Phalcon\Db\Adapter\Pdo\Postgresql
{
    public static $lastStatement;

    /**
     * @param string $sqlStatement
     * @param null $bindParams
     * @param null $bindTypes
     * @return \Phalcon\Db\ResultInterface
     */
    public function query($sqlStatement, $bindParams = null, $bindTypes = null)
    {
        self::$lastStatement = $sqlStatement;
        return parent::query($sqlStatement, $bindParams, $bindTypes);
    }
}
