<?php
namespace Shark\Module\Rbac\Models;

use Phalcon\Mvc\Model;

/**
 * This is the model class for table "Resources".
 *
 * @property integer $id
 * @property string $roleId
 * @property string $resourcesKey
 * @property string $action
 */
class Resources extends Model
{
    public $id;

    public $roleId;

    public $key;

    public $action;

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
            'role_id' => 'roleId',
            'key' => 'key',
            'action' => 'action'
        );
    }
}