<?php

namespace MagicalGirl\Component\DB;

class QueryParameterHolder
{
    private $parameter = array();
    private $fields = array();

    public function __construct(array $parameter = array())
    {
        $this->setParameters($parameter);
    }

    private function setParameters(array $parameter)
    {
        foreach ($parameter as $key => $value) {
            $this->setParameter($key, $value);
        }
    }

    public function setParameter($key, $value)
    {
        $this->parameter[$this->getPlaceholder($key)] = $value;
        $this->fields[] = $key;
    }

    public function getPlaceholder($key)
    {
        return ':' . $key;
    }

    public function getPlaceholders()
    {
        return array_keys($this->parameter);
    }

    public function getParameter($key)
    {
        if (!$this->fieldExists($key)) {
            return null;
        }

        return $this->parameter[$this->getPlaceholder($key)];
    }

    public function getParameterArray()
    {
        return $this->parameter;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function getFieldStrings()
    {
        return array_map(array($this, 'getFieldString'), $this->fields);
    }

    public function getFieldString($field)
    {
        return '`' . $field . '`';
    }

    public function fieldExists($key)
    {
        return array_key_exists($this->getPlaceholder($key), $this->parameter);
    }

}
