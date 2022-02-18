<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Member extends CI_Model{
	
	function __construct() {
        // Set table name
        $this->table = 'members';
    }
	
    /*
     * Fetch members data from the database
     * @param array filter data based on the passed parameters
     */
	 
	function getTabledata($params = array(),$fm){

		$layouts = $fm->listLayouts();
		$layout=$fm->getLayout($layouts[1]); 
		$alllistfields=$layout->listFields();

		$find = $fm->newFindAllCommand($layout->getName());
		
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
					$getresult = $result_exec->records;
				
					$result=array();
					foreach($getresult as $key=>$singlerec){
						for($i=0;$i<count($alllistfields);$i++){
						   $result[$key][$alllistfields[$i]]=$singlerec->fields[$alllistfields[$i]][0];
						 }
						$result[$key]['recordId']=$singlerec->recordId;
					}
				}catch(Exception $e){
					$result=false;
			    }
				
			}
		}
		
		return $result;
	}
	
	
	function getIdData($params = array(),$fm){
		$layouts = $fm->listLayouts();
		$layout=$fm->getLayout($layouts[1]); 
		
		 try {
					$record = $fm->getRecordByID($layouts[1], $params['id']);
				
					$result=array();
					foreach($record->fields as $key=>$singlerec){
						$result[$key]=$singlerec[0];
					}
					$result['recordId']=$record->recordId;
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
    public function insert($data = array(),$fm=null) {
		if(!empty($data)){
			  $layouts = $fm->listLayouts();
			  //$newRecord = $fm->newAddCommand($layouts[1]);
			  $newRecord = $fm->createRecord($layouts[1]);
			  
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
    public function update($data, $id,$fm=null) {
		if(!empty($data) && !empty($id)){
			
			try {
			$layouts = $fm->listLayouts();
			$request = $fm->newEditCommand($layouts[1], $id);
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
	public function delete($id,$fm=null){
		
		$layouts = $fm->listLayouts();
		 
		$delCommand = $fm->newDeleteCommand($layouts[1],$id);
		try {
           $result = $delCommand->execute();
		   return true;
        }catch(Exception $e){
			echo "<pre>";
print_r($e->getMessage());
die; 
		  return false;
		}
		
	}
}
