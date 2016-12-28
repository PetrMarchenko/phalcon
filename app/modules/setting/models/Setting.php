<?php
namespace Shark\Module\Setting\Models;

use Phalcon\Mvc\Model;

/**
 * This is the model class for table "setting".
 *
 * @property string $key
 * @property string $value
 */
class Setting extends Model
{
    public $key;

    public $value;

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
            'key' => 'key',
            'value' => 'value',
        );
    }
}