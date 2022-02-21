<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Member extends CI_Model{
	
	function __construct() {
        // Set table name
        $this->table = 'Members';
		$this->load->library('FileMaker_LIB/FileMaker','filemaker');
    }
	
    /*
     * Fetch members data from the database
     * @param array filter data based on the passed parameters
     */
	 
	function getTabledata($params = array()){

		$layout=$this->filemaker->getLayout($this->table); 
		$alllistfields=$layout->listFields();

		$find = $this->filemaker->newFindAllCommand($layout->getName());
		
		if(array_key_exists("conditions", $params)){
            foreach($params['conditions'] as $key => $val){
				$find->addFindCriterion($key, '=='.$val); 
            }
        }
		
		
		if(!empty($params['searchKeyword'])){
			$search = $params['searchKeyword'];
			$find->setLogicalOperator('OR');
			$find->addFindCriterion('first_name', $search);
			$find->setLogicalOperator('OR');
			$find->addFindCriterion('last_name', $search);
			$find->setLogicalOperator('OR');
			$find->addFindCriterion('email', $search);
		}
		
		
		//$totalrows=$result_exec->getFetchCount();
		
		

        if(array_key_exists("returnType",$params) && $params['returnType'] == 'count'){
			try {
				$result_exec = $find->execute();
			    $result=$result_exec->getFetchCount(); 
				
			}catch(Exception $e){
					$result=false;
			}
		}else{
			if(array_key_exists("id", $params)){
				
				$find->addFindCriterion('id', $params['id']);
				try {
					$result_exec = $find->execute();
					$getresult = $result_exec->records;
					$result=array();
					foreach($getresult as $key=>$singlerec){
						for($i=0;$i<count($alllistfields);$i++){
						   $result[$key][$alllistfields[$i]]=$singlerec->fields[$alllistfields[$i]][0];
						 }
					}
				}catch(Exception $e){
					$result=false;
			    }
				
			}else{
				//$find->setPreSortScript('Set Order');
				$find->addSortRule('first_name', 1);
				
				if(array_key_exists("start",$params) && array_key_exists("limit",$params)){
					$find->setRange($params['start'], $params['limit']);
				}elseif(!array_key_exists("start",$params) && array_key_exists("limit",$params)){
					$find->setRange($params['limit']);
				}
				try {
					$result_exec = $find->execute();
					$getresult = $result_exec->getRecords();
				
					$result=array();
					foreach($getresult as $key=>$singlerec){
						for($i=0;$i<count($alllistfields);$i++){
						   $result[$key][$alllistfields[$i]]=$singlerec->_impl->_fields[$alllistfields[$i]][0];
						 }
						$result[$key]['recordId']=$singlerec->_impl->_recordId;
					}
				}catch(Exception $e){
					$result=false;
			    }
				
			}
		}
		
		return $result;
	}
	
	
	function getIdData($params = array()){
		$layout=$this->filemaker->getLayout($this->table); 
		
		 try {
					$record = $this->filemaker->getRecordByID($this->table, $params['id']);
				
					$result=array();
					foreach($record->_impl->_fields as $key=>$singlerec){
						$result[$key]=$singlerec[0];
					}
					$result['recordId']=$record->_impl->_recordId;
				}catch(Exception $e){
					$result=false;
			    }
				return $result;
	}
    function getRows($params = array()){
        $this->db->select('*');
        $this->db->from($this->table);
		
		if(array_key_exists("conditions", $params)){
			foreach($params['conditions'] as $key => $val){
				$this->db->where($key, $val);
			}
		}
		
		if(!empty($params['searchKeyword'])){
			$search = $params['searchKeyword'];
			$likeArr = array('first_name' => $search, 'last_name' => $search, 'email' => $search);
			$this->db->or_like($likeArr);
		}
		
		if(array_key_exists("returnType",$params) && $params['returnType'] == 'count'){
			$result = $this->db->count_all_results();
		}else{
			if(array_key_exists("id", $params)){
				$this->db->where('id', $params['id']);
				$query = $this->db->get();
				$result = $query->row_array();
			}else{
				$this->db->order_by('first_name', 'asc');
				if(array_key_exists("start",$params) && array_key_exists("limit",$params)){
					$this->db->limit($params['limit'],$params['start']);
				}elseif(!array_key_exists("start",$params) && array_key_exists("limit",$params)){
					$this->db->limit($params['limit']);
				}
				
				$query = $this->db->get();
                $result = ($query->num_rows() > 0)?$query->result_array():FALSE;
			}
		}
		
		// Return fetched data
        return $result;
    }
    
	/*
     * Insert members data into the database
     * @param $data data to be insert based on the passed parameters
     */
    public function insert($data = array()) {
		if(!empty($data)){
			 
			  $newRecord = $this->filemaker->createRecord($this->table);
			  
			  
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
	
	/*
     * Update member data into the database
     * @param $data array to be update based on the passed parameters
     * @param $id num filter data
     */
    public function update($data, $id) {
		if(!empty($data) && !empty($id)){
			
			try {
			$request = $this->filemaker->newEditCommand($this->table, $id);
			foreach($data as $key=>$singleres){
					$request->setField($key, $singleres);
			}
	        $request->execute();
			return true;
			}catch(Exception $e){
					$result=false;
			   }

        }
        return false;
    }
	
	/*
     * Delete member data from the database
     * @param num filter data based on the passed parameter
     */
	public function delete($id){
		
		
		$delCommand = $this->filemaker->newDeleteCommand($this->table,$id);
		try {
           $result = $delCommand->execute();
		   return true;
        }catch(Exception $e){
		  return false;
		}
		
	}
}
