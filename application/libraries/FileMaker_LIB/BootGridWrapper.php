<?php
/**
+--------------------------------------------------------------------
| File    : BootGridWrapper.php
| Path    : /application/libraries/BootGridWrapper.php
| Purpose : Contains all functions for fetching data from FM database
|           being formatted specially for bootgrid.
| Created : 10-Nov-2016
| Author  :  Mindfire Solutions.
| Comments :
+--------------------------------------------------------------------
*/

! defined('BASEPATH') ? exit('No direct script access allowed') : '';

// Include FileMakerWrapper
require_once __DIR__ . '/FileMakerWrapper.php';

/**
 * Contains all functions to format FM data as per Bootgrid format.
 * @see FileMakerWrapper
 */
class BootGridWrapper extends FileMakerWrapper
{
    /**
     * @var String - Search keyword.
     */
    protected $search;

    /**
     * @var Integer - Current page no.
     */
    protected $current;

    /**
     * @var Integer no. of rows that needs to be shown on bootgrid.
     */
    protected $rowCount;

    /**
     * @var String $sortKey - The field name that needs to be sorted.
     */
    protected $sortKey;

    /**
     * @var String $sortOrder - The order in which field will be sorted. (asc/desc)
     */
    protected $sortOrder;

    /**
     * @var Array - List of fields that needs to be searched.
     */
    protected $searchFields;

    /**
     * @var Array - Fields that needs to be AND searched.
     */
    protected $bootGridAndWheres = array();

    /**
     * @var Array - Fields those needs to be OR searched.
     */
    protected $bootGridOrWheres = array();

    /**
     * @var Array - Formatted result for bootgrid.
     */
    protected $bootGridData;

    /**
     * @var Array - Fields being used for pricing.
     */
    protected $priceFields = array();

    /**
    * Used to initialize objects.
    *
    * @param void
    * @return void
    */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Sets layout name.
     *
     * @param String $layout - Layout name.
     * @return Object - Current wrapper class object.
     */
    public function setLayout($layout = '')
    {
        $this->layout = $layout;
        return $this;
    }

    /**
     * Sets search keyword field.
     *
     * @param String $search - Search keyword.
     * @return  Object - Current wrapper class object.
     */
    public function setSearch($search = '')
    {
        $this->search = $search;
         return $this;
    }

    /**
     * Sets total no. of rows that needs to be shown.
     *
     * @param Integer $rowCount - No. of rows
     * @return  Object - Current wrapper class object.
     */
    public function setTotalRows($rowCount = 0)
    {
        $this->rowCount = $rowCount;
         return $this;
    }

    /**
     * Sets current page no. [used for pagination]
     *
     * @param Integer $current - No. of rows
     * @return  Object - Current wrapper class object.
     */
    public function setCurrentPageNo($current = 0)
    {
        $this->current = $current;
         return $this;
    }

    /**
     * Sets sortkey.
     *
     * @param String $sortKey - Field name that needs to be sorted.
     * @return Object - Current wrapper class object.
     */
    public function setSortKey($sortKey = '')
    {
        $this->sortKey = $sortKey;
         return $this;
    }

    /**
     * Sets sort order.
     *
     * @param String $sortOrder - Order in which field will be sorted. (asc/desc)
     * @return Object - Current wrapper class object.
     */
    public function setSortOrder($sortOrder = 'asc')
    {
        $this->sortOrder = $sortOrder;
         return $this;
    }

    /**
     * Sets search fields.
     *
     * @param Array $orWheres - Fields that need to be OR searched.
     * @return Object - Current wrapper class object.
     */
    public function setSearchFields($orWheres = array())
    {
        if (! empty($orWheres) && isAssoc($orWheres)) {
            $this->bootGridOrWheres = $orWheres;
        }
         return $this;
    }

    /**
     * Sets conditional fields.
     *
     * @param Array $andWheres - Fields that need to be AND searched.
     * @return Object - Current wrapper class object.
     */
    public function setConditionalFields($andWheres = array())
    {
        if (! empty($andWheres) && isAssoc($andWheres)) {
            $this->bootGridAndWheres = $andWheres;
        }

        return $this;
    }

    /**
     * Sets pricing fields.
     *
     * @param String $priceFields - Fields that need to be formatted for price.
     * @return Object - Current wrapper class object.
     */
    public function setPriceFormatFields($priceFields = array())
    {
        $this->priceFields = $priceFields;
        return $this;
    }

    /**
     * Gets bootgrid formatted data.
     *
     * @param Array $fields - Fields that need to be included in the result.
     * @return String - json formatted bootgrid data.
     */
    public function getBootGrid($fields = array())
    {
        $low = ($this->current - 1) * $this->rowCount;
        $high = $this->rowCount;


        $this->bootGridData =  $this->orWheres($this->bootGridOrWheres)
                                    ->andWheres($this->bootGridAndWheres)
                                    ->setPriceFields($this->priceFields)
                                    ->limit($low, $high)
                                    ->orderBy($this->sortKey, $this->sortOrder)
                                    ->get($fields);

        return $this->getJson($fields);
    }

    /**
     * Gets json formatted bootgrid data.
     *
     * @param Array $fields - Fields that need to be included in the result.
     * @return String - json formatted bootgrid data.
     */
    protected function getJson($fields = array())
    {
        $items = array();
        $item = array();
        if (! empty($this->bootGridData)) {
            $records = $this->bootGridData['records'];
            $counter = (($this->current - 1) * $this->rowCount) + 1;

            foreach ($records as $record) {
                $item['counter'] = $counter;
                foreach ($record as $recordKey => $recordValue) {
                    if (! empty($fields) && array_key_exists($recordKey, $fields)) {
                         $item[$recordKey] = $recordValue;
                    } else {
                         $item[$recordKey] = $recordValue;
                    }
                }
                $items[] = $item;
                $counter++;
            }
        }

        // Build json
        return json_encode(array(
            'current' => $this->current,
            'rowCount' => $this->rowCount,
            'rows' => $items,
            'total' => isset($this->bootGridData['foundCount'])
                       ? $this->bootGridData['foundCount']
                       : 0
        ));
    }
}
