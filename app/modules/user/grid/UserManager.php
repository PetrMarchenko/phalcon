<?php

namespace Shark\Module\User\Grid;

use Shark\Module\User\Models\Roles;
use Shark\Module\User\Models\Users;
use Shark\Library\Grid\QueryGrid;
use Shark\Module\User\Models\UsersAuth;
use Shark\Module\User\Models\UsersRoles;

/**
 * Class UserManager
 * @exportlimit("10");
 */
class UserManager extends QueryGrid
{
    public function getTitle()
    {
        return 'User manager';
    }


    public function getQueryBuilder()
    {
        if (!$this->query) {
            $di = \Phalcon\DI::getDefault();
            $query = $di->get('modelsManager')->createBuilder();
            $query->from(array('user' => Users::class));
            $query->join(UsersRoles::class, 'user.id = ur.userId ', 'ur');
            $query->join(Roles::class, 'r.id = ur.roleId', 'r');
            $query->columns(['user.*', 'ur.*', 'r.*']);
            $this->setQueryBuilder($query);
        }
        return $this->query;
    }

    /**
     * @return Users
     */
    protected function getUser()
    {
        return $this->row->user;
    }

    /**
     * @return Roles
     */
    protected function getRole()
    {
        return $this->row->r;
    }

    /**
     * @label("User Id")
     * @property("user.id")
     * @searchable
     * @sortable
     *
     * @return string
     */
    public function columnUserId()
    {
        return $this->getUser()->id;
    }

    /**
     * @label("Date created")
     * @property("user.created")
     * @searchable
     * @datetime
     * @sortable
     *
     * @return string
     */
    public function columnCreate()
    {
        return $this->getUser()->created;
    }

    /**
     * @label("User Name")
     *
     * @return string
     */
    public function columnUserName()
    {
        return $this->getUser()->name;
    }

    /**
     * @label("User role")
     *
     * @return string
     */
    public function columnRole()
    {
        return $this->getRole()->key;
    }

    /**
     * @label("")
     *
     * @return string
     */
    public function columnEdit()
    {
        // @codingStandardsIgnoreStart
        ob_start();
        ?>
        <a href="<?php echo $this->getUrl(array('for' => 'user_edit', 'id' => $this->getUser()->id))?>" class="btn btn-primary">Edit</a>
        <?php
        return ob_get_clean();
        // @codingStandardsIgnoreEnd
    }
}
