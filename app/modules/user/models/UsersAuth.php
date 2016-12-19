<?php
namespace Shark\Module\User\Models;

use Phalcon\Mvc\Model;

/**
 * This is the model class for table "roles".
 *
 * @property integer $id
 * @property string $userId
 * @property string $password
 * @method static UsersAuth findFirstByPassword($password)
 * @method static UsersAuth findFirstByUserId($userId)
 */
class UsersAuth extends Model
{
    public $id;

    public $userId;

    public $password;

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
            'user_id' => 'userId',
            'password' => 'password',
        );
    }
}