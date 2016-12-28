<?php

namespace Shark\Module\Setting\Grid;

use Shark\Library\Grid\QueryGrid;
use Shark\Module\Setting\Models\Setting;

/**
 * Class SettingManager
 * @exportlimit("10");
 */
class SettingManager extends QueryGrid
{
    public function getTitle()
    {
        return 'Setting manager';
    }


    public function getQueryBuilder()
    {
        if (!$this->query) {
            $di = \Phalcon\DI::getDefault();
            $query = $di->get('modelsManager')->createBuilder();
            $query->from(array('setting' => Setting::class));
            $query->columns(['setting.*']);
            $this->setQueryBuilder($query);
        }
        return $this->query;
    }

    /**
     * @label("Key")
     * @property("setting.key")
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
     * @label("Value")
     * @property("setting.value")
     * @searchable
     * @sortable
     *
     * @return string
     */
    public function columnValue()
    {
        return $this->row->value;
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
        <a href="<?php echo $this->getUrl(array('for' => 'setting_edit', 'key' => $this->row->key))?>" class="btn btn-primary">Edit</a>
        <?php
        return ob_get_clean();
        // @codingStandardsIgnoreEnd
    }
}
