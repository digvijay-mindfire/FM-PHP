<?php
/**
+--------------------------------------------------------------------
| File    : FileMakerWrapper.php
| Path    : /application/libraries/FileMakerWrapper.php
| Purpose : Contains all functions for accessing views related to customers.
| Created : 10-Sept-2016
| Author  :  Mindfire Solutions.
| Comments :
+--------------------------------------------------------------------
*/

// Include FileMaker connection file.
require_once __DIR__ . '/FileMakerConnect.php';

/**
* Used to perform all FileMaker CRUD operations.
* @see FileMakerConnect.
*/
class FileMakerWrapper extends FilemakerConnect
{
    /**
     * @var String - Name of layout.
     */
    protected $layout;

    /**
     * @var Array Fields that needs to be searched as OR condition.
     */
    protected $orWheres = array();

    /**
     * @var Array - Fields that needs to be searched as AND condition.
     */
    protected $andWheres = array();

    /**
     * @var String - Limit of no. of records in FM query.
     */
    protected $limit = '';

    /**
     * @var Array - Contains orderby field/value pairs.
     */
    protected $orderby = array();

    /**
     * @var Array - Contains all fields that needs to be used in select query.
     */
    protected $fields = array();

    /**
     * @var Array - Contains total no. of fields including portal fields.
     */
    protected $totalFields = array();

    /**
     * @var Boolean  - Whether need to fetch first record only.
     */
    protected $first = false;

    /**
     * @var Boolean  - Whether need to fetch last record only.
     */
    protected $last = false;

    /**
     * @var Array - FileMaker price fields whose value will be formatted.
     */
    protected $fmPriceFields = array();

    /**
     * @var Array - This contains FM script details.
     */
    protected $performScript = array();

    /**
     * @var Array - It contains all fields to which data needs to be inserted.
     */
    protected $insertFields = array();

    /**
    * @var Integer - It contains the recordId of record.
    */
    protected $recordId = 0;

    /**
    * Used to initialize libraries, models etc.
    *
    * @param void
    * @return void
    */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Used to set layout of model.
     *
     * @param String  $layout - Name of layout
     * @return Object - FM wrapper Object.
     */
    public function setLayout($layout = '')
    {
        $this->layout = $layout;
        return $this;
    }

    /**
     * It sets the fields that needs to be used for OR conditionals.
     *
     * @param Array $orWheres - Fields for OR conditionals.
     * @return Object - FM wrapper Object.
     */
    public function orWheres($orWheres = array())
    {
        if (! empty($orWheres) && isAssoc($orWheres)) {
             $this->orWheres = $orWheres;
        }

        return $this;
    }

    /**
     * Used to set fields that are required for and conditionals.
     *
     * @param Array $andWheres - Fields for AND  conditionals.
     * @return Object - Current FM wrapper Object.
     */
    public function andWheres($andWheres = array())
    {
        if (! empty($andWheres) && isAssoc($andWheres)) {
            $this->andWheres = $andWheres;
        }

         return $this;
    }

    /**
     * Used to set range for any FM query.
     *
     * @param Integer $low - Lowest range value
     * @param Integer $high - Highest range value.
     * @return Object - Current FM wrapper Object.
     */
    public function limit($low = 0, $high = 0)
    {
        $this->limit = array(
            'low' => $low,
            'high' => $high
        );
        return $this;
    }

    /**
     * Used to set sorting order of FM query.
     *
     * @param String $sortKey - FM field that needs to be used for sorting.
     * @param String $sortValue - asc/desc (ascending/descending)
     * @return Object - Current FM wrapper Object.
     */
    public function orderBy($sortKey, $sortValue)
    {
        $this->orderBy = array(
            'sortKey' => $sortKey,
            'sortOrder' => $sortValue
        );
        return $this;
    }

    /**
     * Sets orderby property to empty.
     *
     * @return Object - Current FM wrapper Object.
     */
    public function setOrderByEmpty()
    {
        $this->orderBy = array();
        return $this;
    }

    /**
     * Used to set the first parameter
     * so that query will be used to fetch first record.
     *
     * @param void
     * @return Object - Current FM wrapper Object.
     */
    public function first()
    {
        $this->first = true;
        return $this;
    }

    /**
     * Sets price fields in one array.
     *
     * @param Array $fields - Price fields.
     * @return Object - Current FM wrapper Object.
     */
    public function setPriceFields($fields = array())
    {
        $this->fmPriceFields = $fields;
        return $this;
    }

    /**
    * Sets recordId
    *
    * @param Integer $recordId - FM internal id of record.
    * @return Object - FileMaker RecordObject.
    */
    public function setRecordId($recordId)
    {
        $this->recordId = $recordId;
        return $this;
    }

    public function getRecord()
    {
        $record =   $this->db
                         ->getRecordById($this->layout, $this->recordId);
        $fields = $record->getFields();

        $result = array();

        foreach ($fields as $field) {
            $result[$field] = $record->getField($field);
        }

        return $result;
    }

    /**
     * Gets value list from associated field.
     *
     * @param String $valueListName - Name of valuelist.
     * @param String $recordId - Id of FM record.
     * @return String - Value list separated by some separator.
     */
    public function getValueList($valueListName = '', $recordId = '')
    {
        $layoutObject = $this->db->getLayout($this->layout);
        $fieldObject = $layoutObject->getField($valueListName);
        return $fieldObject->getValueList($recordId);
    }

    /**
     * Updates record on FileMaker.
     *
     * @param String $recordId - Id of FM record.
     * @param Array $fieldValues - Field/value pairs that needs
     *                              to be updated on FM file.
     * @return Array - FM Result
     */
    public function update($recordId = '', $fieldValues = array())
    {
        $updateResponse = array();
        if (empty($fieldValues)) {
            return false;
        }

        // Excute edit command.
        $editCommand = $this->db->newEditCommand($this->layout, $recordId);
        foreach ($fieldValues as $field => $value) {
            $editCommand->setField($field, $value);
        }

        $results = $editCommand->execute();

        // Check for error
        if ($this->isFileMakerError($results)) {
            return $updateResponse;
        }

        $updateResponse = $this->fetchRecords($results);
        return $updateResponse;
    }

    /**
     * Used to set fields to which values needs to be inserted.
     *
     * @param Array  $insertFields - Fields that needs to be inserted.
     * @return Object - Current FM wrapper Object.
     */
    public function create($insertFields = array())
    {
        $this->insertFields = $insertFields;
        return $this;
    }

    /**
     * Gets create command for insertion.
     *
     * @param void
     * @return Object - FM command object
     */
    public function getCreateCommand()
    {
        $createCommand = $this->db->newAddCommand($this->layout);

        foreach ($this->insertFields as $fieldName => $fieldValue) {
            $createCommand->setField($fieldName, $fieldValue);
        }

        return $createCommand;
    }

    /**
     * Deletes portal record id.
     *
     * @param String $recordId - internal id of FM record.
     * @param String $portalRecordId - Id of portal record.
     * @param String $portalName - Name of FM portal.
     */
    public function deletePortalRecord($recordId = '', $portalRecordId = '', $portalName = '')
    {
        $deleteResponse = array();
        $results = array();

        // Get record id , then portal, then portal record id and then delete it.
        $record = $this->db->getRecordById($this->layout, $recordId);
        $portalRecords = $record->getRelatedSet($portalName);
        foreach ($portalRecords as $portalRecord) {
            $relatedSetRecordId = $portalRecord->getRecordId();
            if ($portalRecordId === $relatedSetRecordId) {
                $results = $portalRecord->delete();
                break;
            }
        }

        // Check for FM error.
        if ($this->isFileMakerError($results)) {
            return $deleteResponse;
        }

        $deleteResponse = ! empty($results)
                          ? $this->fetchRecords($results)
                          : $deleteResponse;
        return $deleteResponse;
    }

    /**
     * Sets FM script that needs to be excuted from web.
     *
     * @param String $scriptName - Name of FM script.
     * @param String $scriptParams - Name of FM script param as one string.
     * @return Object - Current FM wrapper Object.
     */
    public function setScript($scriptName = '', $scriptParams = '')
    {
        $this->performScript = array(
            'scriptName' => $scriptName,
            'scriptParams' => $scriptParams
        );
        return $this;
    }

    /**
     * Executes FM script from web.
     *
     * @param void
     * @return Object - FM result script command object.
     */
    public function performScript()
    {
        return $this->db->newPerformScriptCommand(
            $this->layout,
            $this->performScript['scriptName'],
            $this->performScript['scriptParams']
        );
    }

    /**
     * Gets recently created record from FM layout.
     *
     * @param void
     * @return Array - FM result.
     */
    public function getLatestRecord()
    {
        $result = array();
        $allRecordCommand = $this->db->newFindAllCommand($this->layout);
        $result = $allRecordCommand->execute();

        if ($this->isFileMakerError($result)) {
            return $result;
        }

        $this->last = true;
        return $this->fetchRecords($result);
    }

    /**
     * It executes required command and returns the final formatted result.
     *
     * @param Array $fields - Fields which needs to be included in result.
     * @return Array  - FM result with FM field value pairs.
     */
    public function get($fields = array())
    {
        // Sets fields for conditionals.
        $this->setFields($fields);

        // Fetch required FM command Object.
        if (empty($this->performScript) &&  empty($this->insertFields)) {
            $findCommand = !empty($this->orWheres)
                           ? $this->fmCompoundFind($fields)
                           :  $this->fmFindCommand($fields);
        } elseif (! empty($this->insertFields)) {
            $findCommand = $this->getCreateCommand();
        } else {
            $findCommand = $this->performScript();
        }

        // Set range.
        if (! empty($this->limit)) {
            //$this->setLimit($findCommand);
             $findCommand->setRange($this->limit['low'], $this->limit['high']);
        }

        // Sort records by field name.
        if (! empty($this->orderBy) && isAssoc($this->orderBy)) {
            $sortOrder =  $this->orderBy['sortOrder'] === 'asc'
                      ? FILEMAKER_SORT_ASCEND : FILEMAKER_SORT_DESCEND;
            // Add sorting rule.
            $findCommand->addSortRule(
                $this->orderBy['sortKey'],
                1,
                $sortOrder
            );
        }

        // Execute and return formatted result.
        $results = $findCommand->execute();

        if ($this->isFileMakerError($results)) {
            return [];
        }

        return $this->fetchRecords($results);
    }

    /**
     * It formats result , checks for portal records.
     *
     * @param Object $results - FM result Object
     * @return Array - FM formatted result array.
     */
    protected function fetchRecords($results = array())
    {
        // Gets first or last record as per requirement.
        $records = $this->first
                   ? $results->getFirstRecord()
                   : $results->getRecords();
        $records = $this->last
                   ? $results->getLastRecord()
                   : $records;
        $relatedSets = $results->getRelatedSets();
        $relatedSetResult = array();
        $fmResult = array();
        $fmResults = array();
        $this->totalFields = $this->fields;

        // Format result data.
        foreach ($records as $record) {
            $relatedSetResult[] = $this->getRelatedSetResult($record, $relatedSets);

            if (empty($this->fields)) {
                $this->setFields($results->getFields());
            }

            foreach ($this->fields as $field) {
                $fieldValue = $record->getField($field);
                $fmResult[$field] = ! empty($this->fmPriceFields) &&
                                    in_array($field, $this->fmPriceFields)
                                     ? getFormattedPrice($fieldValue)
                                     : $fieldValue;
            }
            $fmResult['recordId'] = $record->getRecordId();
            $fmResults['records'][] = $fmResult;
        }

        $fmResults['portals'] =  $relatedSetResult;
        $fmResults['foundCount'] = $results->getFoundSetCount();

        return $fmResults;
    }

    /**
     * Gets relatedset result from fm record.
     *
     * @param Object $record - FM record object
     * @param Array $relatedSets - Set of relatedsets from which data will be fetched.
     * @return Array - Portal result.
     */
    protected function getRelatedSetResult($record, $relatedSets)
    {
        $portalRecord = array();
        $portalRecords = array();
        $portalResult = array();

        if (empty($relatedSets)) {
            return $portalResult;
        }

        // Loop through all relatedsets.
        foreach ($relatedSets as $relatedSet) {
            // Get relatedset record.
            $relatedSetObj = $record->getRelatedSet($relatedSet);

            if (!$this->isFileMakerError($relatedSetObj)) {
                // Get relatedset fields and set it.
                $relatedSetFields = $this->getRelatedSetFields($relatedSetObj);
                $this->setFields($this->getRecordFields($relatedSetFields));
                $counter = 1;

                // Get relatedset record data and format it as per pricing & string fields.
                foreach ($relatedSetObj as $relatedSetRecord) {
                    foreach ($relatedSetFields as $relatedSetField) {
                        $relatedSetFieldValue = $relatedSetRecord->getField($relatedSetField);
                        $portalRecord[$relatedSetField] =   ! empty($this->fmPriceFields) &&
                                                            in_array($relatedSetField, $this->fmPriceFields)
                                                             ? getFormattedPrice($relatedSetFieldValue)
                                                             : $relatedSetFieldValue;
                    }

                    $portalRecord['counter'] = $counter;
                    $portalRecord['portalRecordId'] = $relatedSetRecord->getRecordId();
                    $portalRecords[] = $portalRecord;
                    $counter++;
                }
            }

            $portalResult[$relatedSet] = $portalRecords;
        }

        return $portalResult;
    }

    /**
     * Gets relatedset fields out of all fields.
     *
     * @param Object $relatedSetObj - Relatedset record Object
     * @return Array - List of relatedset fields.
     */
    protected function getRelatedSetFields($relatedSetObj)
    {
        $this->fields = $this->totalFields;
        $relatedSetFields = $relatedSetObj[0]->getFields();

        // Separate relatedset fields out of all fields.
        if (! empty($this->fields)) {
            $relatedSetFields = array_values(
                array_intersect($relatedSetFields, $this->fields)
            );
        }

        return $relatedSetFields;
    }

    /**
     * Gets FM record fields out of all fields.
     *
     * @param Array $relatedSetFields - List of relatedset fields
     * @return Array  - List of FM record fields.
     */
    protected function getRecordFields($relatedSetFields = array())
    {
        return  array_diff($this->fields, $relatedSetFields);
    }

    /**
     * Sets FM fields.
     *
     * @param Array $fields - List of FM fields.
     * @return Array  - List of FM fields.
     */
    protected function setFields($fields = array())
    {
        $this->fields = $fields;
    }

    /**
     * Checks for FM error.
     *
     * @param Object $results - FM result.
     * @return Boolean - true/false depending upon error/no error
     */
    protected function isFileMakerError($results)
    {
        if (FileMaker::isError($results)) {
            log_message('error', $results->getMessage());
            return true;
        }

        return false;
    }

    /**
     * Sets sort order.
     *
     * @param Object $findCommand - FM findcommand object
     * @return void
     */
    protected function setSortOrder($findCommand)
    {
        $sortOrder =  $this->orderBy['sortOrder'] === 'asc'
                      ? FILEMAKER_SORT_ASCEND : FILEMAKER_SORT_DESCEND;
         // Add sorting rule.
        $findCommand->addSortRule(
            $this->orderBy['sortKey'],
            1,
            $sortOrder
        );
    }

    /**
     * Sets range to FM queries.
     *
     * @param Object $findCommand - FM findcommand object
     * @return void
     */
    protected function setLimit($findCommand)
    {
      // Add range.
        $findCommand->setRange($this->limit['low'], $this->limit['high']);
    }

    /**
     * Perform compoundfind on FM layouts.
     *
     * @param Array $fields - List of fields that needs to be present
     *                        in compound find result.
     * @return Object - FM command object
     */
    protected function fmCompoundFind($fields = array())
    {
        // Make new compound find request
        $findCommand = $this->db
                            ->newCompoundFindCommand($this->layout);

        $i = 1;

        foreach ($this->orWheres as $orWhereKey => $orWhereValue) {
            // Make one find request per each OR column.
            ${'findRequest' . $i} = $this->db
                                         ->newFindRequest($this->layout);

            ${'findRequest' . $i}->addFindCriterion($orWhereKey, $orWhereValue);

            // Include all OR columns with each and every and column.
            foreach ($this->andWheres as $andWhereKey => $andWhereValue) {
                 ${'findRequest' . $i}->addFindCriterion(
                     $andWhereKey,
                     $andWhereValue
                 );
            }

            // Add find requests to compound find command
            $findCommand->add($i, ${'findRequest' . $i});
            $i++;
        }

        return $findCommand;
    }

    /**
     * Perform simple find operation on layout.
     *
     * @param Array $fields - List of fields that needs to be included in FM find result.
     * @return Object - FM command object
     */
    protected function fmFindCommand($fields = array())
    {
        $findCommand = $this->db->newFindCommand($this->layout);

        if (! empty($this->andWheres)) {
            foreach ($this->andWheres as $andWhereKey => $andWhereValue) {
                $findCommand->addFindCriterion($andWhereKey, $andWhereValue);
            }
        }

        return $findCommand;
    }

     /**
     * Clears values
     *
     * @return void
     */
    public function clearValues()
    {
        $this->first = false;
        $this->last = false;
        $this->orderBy = [];
        $this->layout = '';
        $this->orWheres = [];
        $this->andWheres = [];
        $this->limit = '';
        $this->fields = [];
        $this->totalFields = [];
        $this->fmPriceFields = [];
        $this->performScript = [];
        $this->postScript = [];
        $this->insertFields = [];
        $this->recordId = 0;
        
        return $this;
    }
}
