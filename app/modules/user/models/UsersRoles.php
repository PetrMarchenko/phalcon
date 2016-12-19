<?php
namespace Shark\Module\User\Models;

use Phalcon\Mvc\Model;
use Shark\Module\User\Models\Roles;

/**
 * This is the model class for table "roles".
 *
 * @property integer $id
 * @property string $userId
 * @property string $roleId
 */
class UsersRoles extends Model
{
    public $id;

    public $userId;

    public $roleId;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setReadConnectionService('dbSlave');
        $this->setWriteConnectionService('dbMaster');

        $this->hasOne(
            'roleId',
            Roles::class,
            'id',
            ['alias' => 'roles']
        );
    }

    /**
     * Independent Column Mapping.
     */
    public function columnMap()
    {
        return array(
            'id' => 'id',
            'user_id' => 'userId',
            'role_id' => 'roleId',
        );
    }
}