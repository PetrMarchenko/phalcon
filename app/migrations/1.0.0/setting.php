<?php

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class SettingMigration_100
 */
class SettingMigration_100 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('setting', [
                'columns' => [
                    new Column(
                        'key',
                        [
                            'type' => Column::TYPE_CHAR,
                            'notNull' => true,
                            'size' => 50,
                            'after' => 'id'
                        ]
                    ),
                    new Column(
                        'value',
                        [
                            'type' => Column::TYPE_CHAR,
                            'notNull' => true,
                            'size' => 254,
                            'after' => 'key'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('setting_key_fk', ['key'], null)
                ],
                'options' => [
                    'TABLE_TYPE' => 'BASE TABLE',
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
            "setting",
            [
                'EXPORT_LIMIT',
                "1000",
            ],
            [
                "key",
                "value",
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
