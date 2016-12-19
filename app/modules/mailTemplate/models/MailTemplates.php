<?php
namespace Shark\Module\MailTemplate\Models;

use Phalcon\Mvc\Model;


/**
 * This is the model class for table "mail_templates".
 *
 * @property integer $id
 * @property string $key
 * @property string subject
 * @property string $name
 * @property string $body
 * @property string $created
 * @property string $updated
 * @method static MailTemplates findFirstByKey($key)
 * @method static MailTemplates findFirstById($id)
 */
class MailTemplates extends Model
{
    public $id;

    public $key;

    public $subject;

    public $name;

    public $body;

    public $created;

    public $updated;

    public function getSource()
    {
        return 'mail_templates';
    }

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
            'subject' => 'subject',
            'name' => 'name',
            'body' => 'body',
            'created' => 'created',
            'updated' => 'updated',
        );
    }

    /**
     * Replace placeholders in template to concrete data
     *
     * @param array $placeholders
     */
    public function replace(array $placeholders)
    {
        foreach ($placeholders as $placeholderName => $value) {
            $this->body = str_replace("{{{$placeholderName}}}", $value, $this->body);
        }
    }
}