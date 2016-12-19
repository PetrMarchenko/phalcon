<?php

namespace Shark\Module\MailTemplate\Grid;

use Shark\Module\MailTemplate\Models\MailTemplates;
use Shark\Library\Grid\QueryGrid;

/**
 * Class MailTemplateGrid
 */
class MailTemplateGrid extends QueryGrid
{
    public function getTitle()
    {
        return 'MailTemplate manager';
    }


    public function getQueryBuilder()
    {
        if (!$this->query) {
            $di = \Phalcon\DI::getDefault();
            $query = $di->get('modelsManager')->createBuilder();
            $query->from(array('mailTemplates' => MailTemplates::class));
            $this->setQueryBuilder($query);
        }
        return $this->query;
    }


    /**
     * @label("Id")
     * @property("mailTemplates.id")
     * @searchable
     * @sortable
     *
     * @return string
     */
    public function columnUserId()
    {
        return $this->row->id;
    }

    /**
     * @label("Date create")
     * @property("user.created")
     * @searchable
     * @datetime
     * @sortable
     *
     * @return string
     */
    public function columnCreate()
    {
        return $this->row->created;
    }

    /**
     * @label("Key")
     * @property("mailTemplates.key")
     * @searchable
     * @sortable
     *
     * @return string
     */
    public function columnKey()
    {
        return $this->row->key;
    }

    /**
     * @label("Subject")
     * @property("mailTemplates.subject")
     * @searchable
     * @sortable
     *
     * @return string
     */
    public function columnSubject()
    {
        return $this->row->subject;
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
        <a href="<?php echo $this->getUrl(array('for' => 'mail_template_edit', 'id' => $this->row->id))?>" class="btn btn-primary">Edit</a>
        <?php
        return ob_get_clean();
        // @codingStandardsIgnoreEnd
    }
}
