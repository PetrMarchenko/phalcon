<?php
namespace Shark\Module\User\Models;

use Phalcon\Mvc\Model;

/**
 * This is the model class for table "roles_inherited".
 *
 * @property integer $id
 * @property string $roleId
 * @property string $parentsRoleId
 */
class RolesInherited extends Model
{
    public $id;

    public $roleId;

    public $parentsRoleId;

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
            'parents_role_id' => 'parentsRoleId',
        );
    }
}