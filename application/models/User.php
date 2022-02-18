<?php defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Model{
    function __construct() {
		// Set table name
        $this->table = 'users';
    }
	
	/*
     * Fetch user data from the database
     * @param array filter data based on the passed parameters
     */
	 
	 function getTabledata($params = array(),$fm=null){
//here  we are getting user talbe data		
		$layouts = $fm->listLayouts(); // getting layouts like uers and member
		$layout=$fm->getLayout($layouts[0]);  // getiing layouts details 0=>users and 1=> members
		$alllistfields=$layout->listFields(); // all users talbel fields
		$result=array();
	
		$find = $fm->newFindCommand($layout->getName()); //command to fm to get data from users table
		
		if(array_key_exists("conditions", $params)){
            foreach($params['conditions'] as $key => $val){
				$find->addFindCriterion($key, '=='.$val); // conditions apply if needed
            }
        }
		//$totalrows=$result_exec->getFetchCount();
		
		

        if(array_key_exists("returnType",$params) && $params['returnType'] == 'count'){
			
			try {
				
				 $result_exec = $find->execute(); // command executed
				 $result=$result_exec->getFetchCount(); // counting total rows
				
			 }catch(Exception $e){
					$result=false;
				}
			 
		}else{
			if(array_key_exists("id", $params) || $params['returnType'] == 'single'){
				if(!empty($params['id'])){
				$find->addFindCriterion('id', $params['id']);
				}
				
				try {
				    $result_exec = $find->execute();
					if($result_exec->getFetchCount()>0){
					$getresult = $result_exec->records; // getting records from command
						
						foreach($getresult as $key=>$singlerec){
							for($i=0;$i<count($alllistfields);$i++){
							   $result[$key][$alllistfields[$i]]=$singlerec->fields[$alllistfields[$i]][0];
							 }
						}
					}
				}catch(Exception $e){
					$result=false;
				}
				
				
				
			}else{ 
				$find->addSortRule('id', 1,$fm->SORT_DESCEND);
				
				if(array_key_exists("start",$params) && array_key_exists("limit",$params)){
					$find->setRange($params['start'], $params['limit']);
				}elseif(!array_key_exists("start",$params) && array_key_exists("limit",$params)){
					$find->setRange($params['limit']);
				}
				
				try {
					$result_exec = $find->execute();
					if($result_exec->getFetchCount()>0){
						$getresult = $result_exec->records;
						foreach($getresult as $key=>$singlerec){
							for($i=0;$i<count($alllistfields);$i++){
							   $result[$key][$alllistfields[$i]]=$singlerec->fields[$alllistfields[$i]][0];
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

    function getRows($params = array()){
        $this->db->select('*');
        $this->db->from($this->table);
        
        if(array_key_exists("conditions", $params)){
            foreach($params['conditions'] as $key => $val){
                $this->db->where($key, $val);
            }
        }
        
        if(array_key_exists("returnType",$params) && $params['returnType'] == 'count'){
            $result = $this->db->count_all_results();
        }else{
            if(array_key_exists("id", $params) || $params['returnType'] == 'single'){
				if(!empty($params['id'])){
					$this->db->where('id', $params['id']);
				}
                $query = $this->db->get();
                $result = $query->row_array();
            }else{
                $this->db->order_by('id', 'desc');
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
     * Insert user data into the database
     * @param $data data to be inserted
     */
	 
	  public function insert($data = array(),$fm=null) {
        if(!empty($data)){
			
			$layouts = $fm->listLayouts();
			
			$newRecord = $fm->createRecord($layouts[0]);
			
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
				   echo "<pre>";
print_r($e->getMessage());
die; 
					$result=false;
			   }
        }
        return false;
    }
}