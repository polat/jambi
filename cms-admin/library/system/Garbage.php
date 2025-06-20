<?php

/**
 *
 * Class Garbage
 *
 */
class Garbage
{
    /**
     * Loader class instance
     * @var object $Loader
     */
    public $Loader;

    /**
     * Garbage constructor.
     *
     * @param $Loader Loader object
     */
    public function __construct(Loader $Loader)
    {
        $this->Loader = $Loader;
        $this->tables = array_keys($this->Loader->tableStructure);
    }

    /**
     *
     * @access public
     */
    public function removeUnusedTables() {
        $trashTables = array_diff($this->Loader->Db->getDbTables(), $this->tables);
    }

    /**
     *
     * @access public
     */
    public function removeUnusedFields() {

    }

    /**
     *
     * @access public
     */
    public function removeUnusedLabels() {

    }

    /**
     *
     * @access public
     */
    public function emptyTrash() {

    }

    /**
     *
     * @access public
     */
    public function removeUnusedLocales() {

    }

    /**
     *
     * @access public
     */
    public function removeUnusedModules() {

    }

    /**
     *
     * @access public
     */
    public function removeUnusedUploads() {

    }
}