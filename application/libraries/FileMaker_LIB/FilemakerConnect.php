<?php
/**
+--------------------------------------------------------------------
| File    : BootGridWrapper.php
| Path    : /application/libraries/BootGridWrapper.php
| Purpose : Connects to filemaker db
| Created : 20-Oct-2015
| Author  :  Mindfire Solutions.
| Comments :
+--------------------------------------------------------------------
*/

! defined('BASEPATH') ? exit('No direct script access allowed') : '';

/**
* It contains methods used for connecting magento SOAP api
*
*/
class FilemakerConnect
{
    /**
    * FileMaker PHP API Object
    */
    public $db;

    /*
    * Used to contain codeigniter instance
    */
    private $ci;

    /**
    * Used to initialize objects
    *
    * @param String $dbConfig - dbconfig filename
    * @return Object - Filemaker connection object
    *
    */
    public function __construct()
    {
        // provide access to CodeIgniter Resources
        $this->ci =& get_instance();

        // create FM Database object
        require_once 'FileMaker.php';
        $this->ci->config->load('filemaker_database');

        $this->db = new FileMaker(
            $this->ci->config->item('database'),
            $this->ci->config->item('hostname'),
            $this->ci->config->item('username'),
            $this->ci->config->item('password')
        );
    }

    public function getContainerData($url = '')
    {
        return $this->db->getContainerData($url);
    }

    public function getFMInstance()
    {
        return $this->db;
    }
}
