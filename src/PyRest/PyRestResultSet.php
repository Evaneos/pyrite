<?php

namespace Pyrite\PyRest;


class PyRestResultSet
{
    /**
     * @return Object[] objects
     */
    protected $objects = array();
    /**
     * @return int page
     */
    protected $page = 1;
    /**
     * @return int countTotal
     */
    protected $countTotal = 0;

    /**
     * @return int countPerPage
     */
    protected $countPerPage = 20;

    /**
     * @return int countPerPage
     */
    public function getCountPerPage() {
        return $this->countPerPage;
    }
    /**
     * @param int $value
     * @return PyRestResultSet
     */
    public function setCountPerPage($value) {
        $this->countPerPage = $value;
        return $this;
    }
    /**
     * @return int countTotal
     */
    public function getCount() {
        return $this->countTotal;
    }
    /**
     * @param int $value
     * @return PyRestResultSet
     */
    public function setCount($value) {
        $this->countTotal = $value;
        return $this;
    }
    /**
     * @return int page
     */
    public function getPage() {
        return $this->page;
    }
    /**
     * @param int $value
     * @return PyRestResultSet
     */
    public function setPage($value) {
        $this->page = $value;
        return $this;
    }

    /**
     * Return the number of pages
     * @return int
     */
    public function getNbPages() {
        return ceil($this->countTotal / (($this->countPerPage > 0) ? $this->countPerPage : 1));
    }

    /**
     * @return Object[] objects
     */
    public function getObjects() {
        return $this->objects;
    }
    /**
     * @param Object[] $value
     * @return PyRestResultSet
     */
    public function setObjects(array $value = array()) {
        $this->objects = $value;
        return $this;
    }

}