<?php
namespace Shark\Module\User\Models;

use Phalcon\Mvc\Model;
//use Shark\Module\User\Models\UsersRoles;
use Phalcon\Http\Request;

/**
 * This is the model class for table "users".
 *
 * @property integer $id
 * @property string $name
 * @property string $email
 * @property integer $status
 * @property string $created
 * @property string $updated
 * @property UsersRoles $usersRoles
 * @property UsersAuth $usersAuth
 * @method static Users findFirstById($id)
 * @method static Users findFirstByEmail($email)
 */
class Users extends Model
{
    /** Active user status */
    const STATUS_ACTIVE = 1;

    /** Blocked user status */
    const STATUS_BLOCKED = 2;

    /** Created user status */
    const STATUS_CREATED = 3;

    public $id;

    public $name;

    public $email;

    public $status;

    public $created;

    public $updated;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setReadConnectionService('dbSlave');
        $this->setWriteConnectionService('dbMaster');

        $this->hasOne(
            'id',
            UsersRoles::class,
            'userId',
            ['alias' => 'usersRoles']
        );

        $this->hasOne(
            'id',
            UsersAuth::class,
            'userId',
            ['alias' => 'usersAuth']
        );
    }

    /**
     * Independent Column Mapping.
     */
    public function columnMap()
    {
        return array(
            'id' => 'id',
            'name' => 'name',
            'email' => 'email',
            'status' => 'status',
            'created' => 'created',
            'updated' => 'updated',
        );
    }


    /**
     * Example $data = [
     *     'name' => 'name',
     *     'email' => 'Admin@admin.ua',
     *     'password' => '123456',
     *     'status' => '1' default Users::STATUS_CREATED,
     *     'roleId' => '2' default Roles::ROLE_USERS
     * ]
     *
     * @param $data array
     * @return bool|Users
     */
    public static function created(array $data)
    {
        $user = new Users();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->status = (isset($data['status'])) ? $data['status'] : Users::STATUS_CREATED;

        if (false === $user->save()) {
            return false;
        }

        $usersRoles = new UsersRoles();
        $usersRoles->userId = $user->id;
        $usersRoles->roleId = (isset($data['roleId'])) ? $data['roleId'] : Roles::ROLE_USERS;

        if (false === $usersRoles->save()) {
            return false;
        }

        $usersAuth = new UsersAuth();
        $usersAuth->userId = $user->id;
        $usersAuth->password = sha1($data['password']);

        if (false === $usersAuth->save()) {
            return false;
        }

        return $user;
    }

    /**
     * Example $data = [
     *     'name' => 'name',
     *     'email' => 'Admin@admin.ua',
     *     'password' => '123456',
     *     'status' => '1',
     *     'roleId' => '2'
     * ]
     *
     * @param $data array
     * @param $user Users
     * @return bool|Users
     */
    public static function edit(array $data, Users $user)
    {
        $usersRoles = UsersRoles::findFirstByUserId($user->id);
        if (!$usersRoles) {
            return false;
        }
        $usersRoles->userId = $user->id;
        $usersRoles->roleId = $data['roleId'];
        if (false === $usersRoles->save()) {
            return false;
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->status = $data['status'];

        if (false === $user->save()) {
            return false;
        }

        return $user;
    }
}