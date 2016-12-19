<?php
namespace Shark\Module\User\Models;

use Phalcon\Mvc\Model;

/**
 * This is the model class for table "roles".
 *
 * @property integer $id
 * @property string $key
 */
class Roles extends Model
{
    const ROLE_GUESTS = 1;

    const ROLE_USERS = 2;

    const ROLE_ADMIN = 3;

    public $id;

    public $key;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setReadConnectionService('dbSlave');
        $this->setWriteConnectionService('dbMaster');
    }

    /**
     * Independent Column Mapping.
     */
    public function columnMap()
    {
        return array(
            'id' => 'id',
            'key' => 'key',
        );
    }
}