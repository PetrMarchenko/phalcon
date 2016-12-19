<?php

namespace Shark\Library\Grid;

use Phalcon\Mvc\Url;
use Shark\Library\Timezone;

abstract class AbstractGrid
{
    public $sortBy;
    public $sortDir;
    public $pageSizes = array(5, 10, 20, 50, 100, 'selected' => 200);

    protected $row;
    protected $params;

    protected $summaryOnBottom = true;

    protected $columns;
    protected $allRows;
    protected $options;

    protected $map = [];

    protected $mode = 'html';

    protected $isExportCsv = false;

    const EXPORT_LIMIT = 10000;


    public function initRow()
    {
    }

    /**
     * @return \stdClass
     */
    abstract protected function getPaginatedRows();

    /**
     * @return array
     */
    abstract protected function getAllRows();

    /**
     *
     * @return array
     */
    public function toArray()
    {
        $paginate = $this->getPaginatedRows();

        $data = $this->getData($paginate->items);

        $summary = $this->getSummary();
        if ($summary) {
            if ($this->summaryOnBottom) {
                $data[] = $summary;
            } else {
                array_unshift($data, $summary);
            }
        }

        $limit = $this->params['pageSize'];

        $start = $limit * ($paginate->current - 1) + 1;
        $end = $start + count($paginate->items) - 1;
        $result = array(
            'options' => $this->getOptions(),
            'data' => $data,
            'start' => $start,
            'end' => $end,
            'count' => $paginate->total_items,
            'pages' => $paginate->total_pages,
            'page' => $paginate->current,
        );
        return $result;
    }

    public function setMap(array $array)
    {
        $this->map = $array;
    }

    public function getValueInMap($key)
    {
        return $this->map[$key];
    }

    public function getPropertyConfig($property)
    {
        foreach ($this->getColumns() as $config) {
            if ($config['property'] == $property) {
                return $config;
            }
        }
        return array();
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        if (!$this->columns) {
            $reader = new \Phalcon\Annotations\Adapter\Memory();

            $methods = get_class_methods($this);
            $className = get_class($this);

            $columns = array();
            foreach ($methods as $method) {
                if ('column' == substr($method, 0, 6)) {
                    $reflector = $reader->getMethod($className, $method);

                    if ($reflector->has('property')) {
                        $property = $reflector->get('property')->getArgument(0);
                    } else {
                        $property = substr($method, 6);
                        $property[0] = strtolower($property[0]);
                    }

                    if ($reflector->has('permission')) {
                        $isAllowed = \Phalcon\DI::getDefault()->getShared('permissions');
                        if (isset($isAllowed) && $isAllowed === false) {
                            continue;
                        }

                    }

                    $checkbox = $reflector->has('checkbox');
                    $checkboxArg ='';
                    if ($checkbox) {
                        $checkboxArg = $reflector->get('checkbox')->getArgument(0);
                    }

                    $searchable = $reflector->has('searchable');
                    if ($searchable) {
                        $searchArg = $reflector->get('searchable')->getArgument(0);
                        if ($searchArg) {
                            $searchable = $this->{$searchArg}();
                        }
                    }

                    $inputSizeClass = $reflector->has('inputSizeClass') ? $reflector->get('inputSizeClass')->getArgument(0) : 'small';

                    $sortable = $reflector->has('sortable');
                    if ($sortable) {
                        $sortField = $reflector->get('sortable')->getArgument(0);
                        if ($sortField) {
                            $sortable = $sortField;
                        }
                    }

                    $columns[] = array(
                        'property' => $property,
                        'label' => $reflector->has('label') ? $reflector->get('label')->getArgument(0) : ucfirst($property),
                        'pattern' => $reflector->has('pattern') ? $reflector->get('pattern')->getArgument(0) : '',
                        'sortable' => $sortable,
                        'searchable' => $searchable,
                        'checkbox' => $checkboxArg ?: '&nbsp;',
                        'customSearch' => $reflector->has('customSearch') ? $reflector->get('customSearch')->getArgument(0) : '',
                        'filter' => $reflector->has('filter') ? $reflector->get('filter')->getArguments() : '',
                        'rate' => $reflector->has('rate') ? 1 : 0,
                        'number' => $reflector->has('number') ? ($reflector->get('number')->getArgument(0) ?: 2) : 0,
                        //'currency' => $reflector->has('currency') ? $reflector->get('currency')->getArgument(0) : 0,
                        'percent' => $reflector->has('percent') ? 1 : 0,
                        'date' => $reflector->has('date') ? 1 : 0,
                        'datetime' => $reflector->has('datetime') ? 1 : 0,
                        'summary' => $reflector->has('summary') ? $reflector->get('summary')->getArgument(0) : 0,
                        'category' => $reflector->has('category') ? $reflector->get('category')->getArgument(0) : 0,
                        'bool' => $reflector->has('bool') ? 1 : 0,
                        'method' => $method,
                        'cssClass' => $reflector->has('cssClass') ? $reflector->get('cssClass')->getArgument(0) : '',
                        'default' => $reflector->has('default') ? $reflector->get('default')->getArgument(0) : 0,
                        'inputSizeClass' => $inputSizeClass,
                    );
                }
            }
            $this->columns = $columns;
        }
        return $this->columns;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        if (!$this->options) {
            $this->options = array(
                'sortBy' => $this->sortBy,
                'sortDir' => $this->sortDir,
            );
            $reader = new \Phalcon\Annotations\Adapter\Memory();

            $className = get_class($this);
            $classAnnotations = $reader->get($className)->getClassAnnotations();

            $this->options['exportlimit'] = 100; //GeneralSettings::getExportLimit();
            if (!empty($classAnnotations)) {
                if ($classAnnotations->has('exportlimit')) {
                    $this->options['exportlimit'] = $classAnnotations->get('exportlimit')->getArgument(0);
                }
            }

            foreach (get_class_methods($this) as $method) {
                if ('option' == substr($method, 0, 6)) {
                    $reflector = $reader->getMethod($className, $method);

                    $property = substr($method, 6);
                    $property[0] = strtolower($property[0]);

                    $config = array(
                        'rate' => $reflector->has('rate') ? 1 : 0,
                        'number' => $reflector->has('number') ? ($reflector->get('number')->getArgument(0) ?: 2) : 0,
                        //'currency' => $reflector->has('currency') ? $reflector->get('currency')->getArgument(0) : 0,
                        'percent' => $reflector->has('percent') ? 1 : 0,
                        'date' => $reflector->has('date') ? 1 : 0,
                        'bool' => $reflector->has('bool') ? 1 : 0,
                        'default' => $reflector->has('default') ? $reflector->get('default')->getArgument(0) : 0
                    );

                    $value = $this->{$method}();

                    $this->options[$property] = $this->formatValue($value, $config);
                }
            }
        }
        return $this->options;
    }

    /**
     * @param null $start
     * @param bool $dataOnly
     * @return array
     */
    public function toCsv($start = null, $dataOnly = false)
    {
        $rowset = $this->getAllRows($start);

        $data = $this->getData($rowset, true);

        $strip = array();
        if (!$dataOnly) {
            $labels = array();
            foreach ($this->getColumns() as $config) {
                if (empty($config['label']) || !strip_tags($config['label'])) {
                    $strip[] = $config['property'];
                } else {
                    $label = $config['label'];
                    if (!empty($config['category'])) {
                        $label = $config['category'] . ' '. $label;
                    }
                    $labels[] = $label;
                }
            }

            $summary = $this->getSummary(true);
            if ($summary) {
                if ($this->summaryOnBottom) {
                    $data[] = $summary;
                } else {
                    array_unshift($data, $summary);
                }
            }

            array_unshift($data, $labels);
        }

        foreach ($data as $j => $row) {
            foreach ($strip as $i) {
                unset($data[$j][$i]);
            }
        }

        return $data;
    }

    /**
     * @param $rowset
     * @param bool $clean
     * @return array
     */
    public function getData($rowset, $clean = false)
    {
        $this->isExportCsv = $clean;

        $data = array();
        foreach ($rowset as $i => $row) {
            $data[$i] = $this->getRowData($row, $clean);
        }
        return $data;
    }

    /**
     * @param $row
     * @param bool $clean
     * @return array
     */
    public function getRowData($row, $clean = false)
    {
        $data = array();
        $this->row = $row;
        $this->initRow();

        foreach ($this->getColumns() as $config) {
            $value = $this->{$config['method']}();

            $value = $this->formatValue($value, $config, $clean);

            $data[$config['property']] = $value;
        }
        return $data;
    }

    public function getSummary($clean = false)
    {
        $hasSummary = false;
        $summary = array();
        foreach ($this->getColumns() as $config) {
            $property = $config['property'];

            if (!empty($config['summary'])) {
                $hasSummary = true;

//                if ($clean) {
//                    unset($config['currency']);
//                }
                $value = $this->{$config['summary']}();
                $value = $this->formatValue($value, $config);

                $summary[$property] = $value;
            } else {
                $summary[$property] = '';
            }
        }
        if ($hasSummary) {
            return $summary;
        }
        return null;
    }

    private function formatValue($value, $config, $clean = false)
    {
        if ($clean) {
//            if (isset($config['currency'])) {
//                unset($config['currency']);
//                $config['default'] = '0';
//            }
            if (is_string($value)) {
                $value = strip_tags($value);
            }
        }

        if ($value === null && !empty($config['default'])) {
            $value = $config['default'];
        } else {

            if (!empty($config['checkbox']) && is_array($value)) {
                $value = $this->toCheckbox($value, $clean);
            }
            if (!empty($config['number'])) {
                $value = $this->toNumber($value, $config['number']);
            }
            if (!empty($config['rate'])) {
                $value = $this->toNumber($value, 4);
            }
            if (!empty($config['date'])) {
                $value = $this->toDate($value);
            }
            if (!empty($config['datetime'])) {
                $value = $this->toDatetime($value);
            }
            if (!empty($config['bool'])) {
                $value = $this->toBool($value);
            }
            if (!empty($config['percent'])) {
                $value .= '%';
            }
//            if (!empty($config['currency'])) {
//                $symbol = $config['currency'];
//                $value = $symbol . $value;
//            }
            if (null === $value) {
                $value = '-';
            }
        }

        return $value;
    }

    protected function filterValue($value, $config)
    {
        if ($value !== null && !empty($config['filter'])) {
            if (in_array('integer', $config['filter'])) {
                $value = $this->toNumber($value, 0, '');
            }
            if (in_array('unsigned', $config['filter'])) {
                $value = $this->toUnsigned($value);
            }
        }

        return $value;
    }

    protected function toNumber($value, $decimals = 2, $thSeparator = ',')
    {
        return number_format((float)$value, $decimals, ".", $thSeparator);
    }

    protected function toUnsigned($value)
    {
        return ($value < 0) ? -$value : $value;
    }

    protected function parseNumber($value)
    {
        return preg_replace('%[^\d.-]%', '', $value);
    }

    protected function toBool($value)
    {
        return $value ? 'Y' : 'N';
    }

    protected function toCheckbox($data, $clean = false)
    {
        $checked = isset($data['checked']) && $data['checked'] ? 'checked' : '';

        if ($clean) {
            return isset($data['checked']) ? $this->toBool($checked) : '';
        }
        $classes = '';
        if (!empty($data['class'])) {
            $classes = $data['class'];
        }
        $dataAttr = '';
        if (!empty($data['data'])) {
            foreach ($data['data'] as $attr => $val) {
                $dataAttr .= ' data-' . $attr . '="' . $val . '"';
            }
        }


        $disabled = isset($data['disabled']) && $data['disabled'] ? ' disabled '  : "";

        $name = $data['name'];
        $value = isset($data['value']) ? $data['value'] : "";

        return '<input type="checkbox" class="' . $classes .'" ' . $checked . ' name="' . $name . '" value="' . $value . '"' . $dataAttr . $disabled . ' />';
    }

    protected function toDate($value)
    {
        //return $value;
        if ($value) {
            $tz = \Phalcon\DI::getDefault()->getSession()->get('timezone');
            $timezone = new Timezone($tz);
            return $timezone->modifyDate($value, false, 'd-m-y');
        }
    }

    protected function toDatetime($value)
    {
        //return $value;
        if ($value) {
            $tz = \Phalcon\DI::getDefault()->getSession()->get('timezone');
            $timezone = new Timezone($tz);
            return $timezone->modifyDate($value, false, 'd-m-y H:i:s');
        }
    }

    public function getName()
    {
        $className = get_class($this);
        $parts = explode('\\', $className);

        return end($parts);
    }

    /**
     * @return array
     */
    public function getBooleanSelect()
    {
        return array('N', 'Y');
    }

    public function getId()
    {
        $result = get_class($this);
        $result = explode('\\', $result);
        return end($result);
    }

    public function __get($name)
    {
        if ($name == 'url') {
            return \Phalcon\DI::getDefault()->getShared('url');
        }

        //return parent::__get($name);
    }

    /**
     * @param mixed $uri
     * @param array $args
     * @return string
     */
    public function getUrl($uri = null, $args = null)
    {
        return $this->url->get($uri, $args);
    }

    public function getRowsCount()
    {
        return count($this->allRows);
    }

    /**
     * @param $pos 'top' | 'bottom'
     */
    public function setSummaryPosition($pos)
    {
        $this->summaryOnBottom = ($pos == 'bottom');
    }
}