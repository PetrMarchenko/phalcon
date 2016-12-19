<?php

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class ResourcesRolesMigration_101
 */
class ResourcesMigration_101 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('resources', [
                'columns' => [
                    new Column(
                        'id',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'autoIncrement' => true,
                            'size' => 11,
                            'first' => true
                        ]
                    ),
                    new Column(
                        'role_id',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'size' => 11,
                            'after' => 'id'
                        ]
                    ),
                    new Column(
                        'key',
                        [
                            'type' => Column::TYPE_CHAR,
                            'notNull' => true,
                            'size' => 255,
                            'after' => 'role_id'
                        ]
                    ),
                    new Column(
                        'action',
                        [
                            'type' => Column::TYPE_CHAR,
                            'notNull' => true,
                            'size' => 50,
                            'after' => 'key'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('PRIMARY', ['id'], 'PRIMARY'),
                    new Index('resources_id_fk', ['role_id'], null)
                ],
                'references' => [
                    new Reference(
                        'resources_id_fk',
                        [
                            'referencedSchema' => 'phalcon_starter',
                            'referencedTable' => 'roles',
                            'columns' => ['role_id'],
                            'referencedColumns' => ['id'],
                            'onUpdate' => 'RESTRICT',
                            'onDelete' => 'RESTRICT'
                        ]
                    )
                ],
                'options' => [
                    'TABLE_TYPE' => 'BASE TABLE',
                    'AUTO_INCREMENT' => '1',
                    'ENGINE' => 'InnoDB',
                    'TABLE_COLLATION' => 'utf8_general_ci'
                ],
            ]
        );
    }

    /**
     * Run the migrations
     *
     * @return void
     */
    public function up()
    {
        $this->morph();

        self::$_connection->insert(
            "resources",
            [
                1,
                1,
                "home/index",
                "index"
            ],
            [
                "id",
                "role_id",
                "key",
                "action"
            ]
        );

        self::$_connection->insert(
            "resources",
            [
                2,
                2,
                "home/index",
                "about"
            ],
            [
                "id",
                "role_id",
                "key",
                "action"
            ]
        );

        self::$_connection->insert(
            "resources",
            [
                3,
                3,
                "home/index",
                "contact"
            ],
            [
                "id",
                "role_id",
                "key",
                "action"
            ]
        );

        self::$_connection->insert(
            "resources",
            [
                4,
                3,
                "rbac/index",
                "index"
            ],
            [
                "id",
                "role_id",
                "key",
                "action"
            ]
        );

        self::$_connection->insert(
            "resources",
            [
                5,
                3,
                "rbac/index",
                "save"
            ],
            [
                "id",
                "role_id",
                "key",
                "action"
            ]
        );

        self::$_connection->insert(
            "resources",
            [
                6,
                1,
                "user/login",
                "login"
            ],
            [
                "id",
                "role_id",
                "key",
                "action"
            ]
        );

        self::$_connection->insert(
            "resources",
            [
                7,
                1,
                "user/login",
                "logout"
            ],
            [
                "id",
                "role_id",
                "key",
                "action"
            ]
        );

        self::$_connection->insert(
            "resources",
            [
                8,
                1,
                "user/login",
                "auth"
            ],
            [
                "id",
                "role_id",
                "key",
                "action"
            ]
        );

        self::$_connection->insert(
            "resources",
            [
                9,
                1,
                "user/login",
                "registration"
            ],
            [
                "id",
                "role_id",
                "key",
                "action"
            ]
        );
    }

    /**
     * Reverse the migrations
     *
     * @return void
     */
    public function down()
    {

    }

}
