<?php defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Model{
    function __construct() {
		// Set table name
        $this->table = 'Users';
		$this->load->library('FileMaker_LIB/FileMaker','filemaker');
		
    }
	
	/*
     * Fetch user data from the database
     * @param array filter data based on the passed parameters
     */
	 
	 function getTabledata($params = array()){
		
		
		$layout=$this->filemaker->getLayout($this->table); 
		
		$alllistfields=$layout->listFields();
		$result=array();

		$find = $this->filemaker->newFindCommand($layout->getName());
		
		if(array_key_exists("conditions", $params)){
            foreach($params['conditions'] as $key => $val){
				$find->addFindCriterion($key, '=='.$val); 
            }
        }
		//$totalrows=$result_exec->getFetchCount();
		
		

        if(array_key_exists("returnType",$params) && $params['returnType'] == 'count'){
			
			try {
				
				 $result_exec = $find->execute();
				 if($this->filemaker->isError($result_exec)){
					 return 0;
				 }else{
				    $result=$result_exec->getFetchCount(); 
				 }
				
			 }catch(Exception $e){
					$result=false;
				}
			 
		}else{
			if(array_key_exists("id", $params) || (isset($params['returnType']) &&  $params['returnType']== 'single')){
				if(!empty($params['id'])){
				$find->addFindCriterion('id', $params['id']);
				}

				
				try {
				    $result_exec = $find->execute();
					
					if($this->filemaker->isError($result_exec)){
						return $result=false;
					}
					if($result_exec->getFetchCount()>0){
					$getresult = $result_exec->getRecords();
						
						foreach($getresult as $key=>$singlerec){
							
							for($i=0;$i<count($alllistfields);$i++){
							   $result[$key][$alllistfields[$i]]=$singlerec->_impl->_fields[$alllistfields[$i]][0];
							 }
						}
					}
				}catch(Exception $e){

					$result=false;
				}
				
				
				
			}else{ 
				$find->addSortRule('id', 1,FILEMAKER_SORT_DESCEND);
				
				if(array_key_exists("start",$params) && array_key_exists("limit",$params)){
					$find->setRange($params['start'], $params['limit']);
				}elseif(!array_key_exists("start",$params) && array_key_exists("limit",$params)){
					$find->setRange($params['limit']);
				}
				
				try {
					$result_exec = $find->execute();
					if($result_exec->getFetchCount()>0){
						$getresult = $result_exec->getRecords();
						foreach($getresult as $key=>$singlerec){
							for($i=0;$i<count($alllistfields);$i++){
							   $result[$key][$alllistfields[$i]]=$singlerec->_impl->_fields[$alllistfields[$i]][0];
							 }
						}
					}
				}catch(Exception $e){
					$result=false;
				}
			}
		}
		
		return $result;
	}

   
    
    /*
     * Insert user data into the database
     * @param $data data to be inserted
     */
	 
	  public function insert($data = array()) {
        if(!empty($data)){
			
			
			$newRecord = $this->filemaker->createRecord($this->table);
			
			 // Add created and modified date if not included
            if(!array_key_exists("created", $data)){
                $data['created'] = date("m-d-Y H:i:s");
            }
            if(!array_key_exists("modified", $data)){
                $data['modified'] = date("m-d-Y H:i:s");
            }
			
			foreach($data as $key=>$singleres){
			    $newRecord->setField($key, $singleres);
			}
			
			try {
			       $result = $newRecord->commit();
			       $recordId = $newRecord->getRecordId();
				   return $recordId?$recordId:false;
               }catch(Exception $e){
				  
					$result=false;
			   }
        }
        return false;
    }
}