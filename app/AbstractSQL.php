<?php

/**
 * Abstract SQL class
 *
 * @author Eko Kurniawan <fkurniawan@outlook.com>
 */
abstract class AbstractSQL extends DB\SQL\Mapper
{
    protected $validatedFilters;

    /**
     * table name
     * @var string
     */
    public $tableName;
    protected $filters = [];

    /**
     * Init mapper
     */
    public function __construct()
    {
        $this->tableName || $this->tableName = App::classNameToTable(get_called_class());
        parent::__construct(App::database(), $this->tableName);
        $this->init();
    }

    /**
     * Define init process here
     * @return null
     */
    public function init()
    {}

    /**
     * Perform input validation
     * @return bool
     */
    public function inputValid()
    {
        $filter = Filter::instance();
        if (empty($this->validatedFilters))
            $this->validatedFilters = $filter->validateFilter();

        $true = true;
        foreach ($this->validatedFilters as $key => $value) {
            foreach ($value['filter'] as $filterName => $opt) {
                $result = $filter->$filterName($this->fields[$key]['value'], $opt);
                $this->fields[$key]['valid'] = true;
                if (is_bool($result)) {
                    $true &= $result;
                    $this->fields[$key]['valid'] = $result;
                } else
                    $this->fields[$key]['value'] = $result;
                if ($map = array_search('{this}', $opt))
                    $opt[$map] = $this;
                $rep = [
                    ':field'=>App::titleIze($key),
                    ':val'=>$this->fields[$key]['value'],
                    ':args'=>implode(', ', array_intersect_key($opt, array_flip(array_filter(array_keys($opt), 'is_numeric'))))
                ]+App::prependKey($opt);
                $this->fields[$key]['message'] = str_replace(
                    array_keys($rep),
                    array_values($rep),
                    isset($opt['msg'])?$opt['msg']:'Invalid :field value');
            }
        }

        return $true;
    }

    /**
     * check field valid status
     * @param  string $field
     * @return bool
     */
    public function fieldValid($field)
    {
        return isset($this->fields[$field]) && isset($this->fields[$field]['valid'])?
            $this->fields[$field]['valid']:false;
    }

    /**
     * get field error message
     * @param  string $field
     * @return string|null
     */
    public function fieldError($field)
    {
        return isset($this->fields[$field]) && isset($this->fields[$field]['message'])?
            $this->fields[$field]['message']:null;
    }
}