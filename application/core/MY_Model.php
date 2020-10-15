<?php

class MY_Model extends CI_Model
{
	protected $data = [];

	public function __construct()
	{
		parent::__construct();
		$this->scheme = "production";
		$this->scheme_ctl = substr($this->scheme, 0, strlen($this->scheme) - 3)."CTL";
		$this->load->helper('ict_helper');
	}

	// PROTECTED SECTION

	protected $scheme;
	protected $scheme_ctl;

	protected function getNextNumber($seq, $text){
		$my_number = self::getCurrentNumber($seq);

		if($my_number != null && count($my_number) > 0){
			self::updateNextNumber($seq);
			return $my_number[0]->GEN_NUMBER;
		}

		self::insertNextNumber($seq, $text);
		return self::getNextNumber($seq, $text);
	}

	protected function getHeadColumn($table){
		$field = $this->db->field_data($table);
		return $field;
	}

	protected function getAllData($table, $selection = null, $filter = null, $sort = null, $joins = null, $group_by = null, $limit = null, $offset = null, $distinct = null){
      //NOTES:
      //FORMAT $selection : "field_a, field_b, field_c,...."
      //FORMAT $filter : array(0 => array(0 => "SPECIFICATION", 1 = > "FIELD", 2 => "VALUE"))
      //FORMAT $sort : array(0 => array(0 => "FIELD", 1 = > "ASC / DESC"))
      //FORMAT $joins : array(0 => array("join_table" => "value", "join_on" => "value", "join_heading" => "value"))
		$select = "*";
		if($selection != null ){
			$select = $selection;
		}
		if($distinct != null){
			$this->db->distinct();
		}
		$this->db->select($select);
		$this->db->from($table);
		if($filter != null){
			$this->generateWhereFromFilter($filter);
		}
		if($sort != null){
			
			$this->generateSorting($sort);
		}
		if($joins != null){
			$this->generateJoin($joins);
		}
		if($group_by != null){
			$this->db->group_by($group_by);
		}
		if($limit != null && $offset == null){
			$this->db->limit($limit);
		}
		
		if($limit != null && $offset != null){

			$this->db->limit($limit, $offset);
		}
		
		$query = $this->db->get();
		return $query->result_array();
	}

	protected function getData($table, $id){
		$query = $this->db->get_where($table, $id);
		return $query->row_array();
	}

	protected function insertDataAutoIncrement($table, $input){
		$result = "";
		try {
        // $input = $this->setAuditInformation($input, 1); 

			$this->db->trans_begin(); 
			$this->db->insert($table, $input);
			$result = $this->db->insert_id();

			if ($this->db->trans_status() === FALSE){$this->db->trans_rollback();}
			else{$this->db->trans_commit();}       

		} catch (Exception $e) {
			$this->session->set_flashdata($e);
		}

		return $result;
	}

	protected function insertData($table, $input){
		$result = "";
		try {
        // $input = $this->setAuditInformation($input, 1); 

			$this->db->trans_begin(); 
			$this->db->insert($table, $input);

			if ($this->db->trans_status() === FALSE){$this->db->trans_rollback(); return false;}
			else{$this->db->trans_commit(); return true;}       

		} catch (Exception $e) {
			$this->session->set_flashdata($e);
		}

		return $result;
	}

	protected function insertDataOracle($table, $input){
		$result = "";
		try {
        // $input = $this->setAuditInformation($input, 1); 

			$this->db->trans_begin(); 
			if($input != null){
				foreach ($input as $key => $value) {
					if(!is_array($value)){
						$this->db->set($key, $value);
					}
					else{
						if(key($value) == 'DATE_VALUE')
							$this->db->set($key, "TO_DATE('".$value["DATE_VALUE"]."','yyyy/mm/dd')", false);
						else if(key($value) == 'DATETIME_VALUE')
							$this->db->set($key, "TO_DATE('".$value["DATE_VALUE"]."','yyyy/mm/dd HH24:MI:SS')", false);
						
					}
				}
			}

			$this->db->insert($table);

			if ($this->db->trans_status() === FALSE){$this->db->trans_rollback();}
			else{$this->db->trans_commit();return true;}       

		} catch (Exception $e) {
			$this->session->set_flashdata($e);
		}

		return $result;
	}

	public function updateDataOracle($table, $input, $where, $extra = null){
		$result = "";
		try {
        // $input = $this->setAuditInformation($input, 1); 

			$this->db->trans_begin(); 
			if($input != null){
				foreach ($input as $key => $value) {
					$this->db->set($key, $value);
				}
			}

			if ($extra != NULL)
			{
				foreach ($extra as $key => $value)
				{
					if ($value['TYPE'] == 'DATE')
					{
						$this->db->set($key, "TO_DATE('" . $value['DATA'] . "', 'yyyy/mm/dd HH24:MI:SS')", FALSE);
					}
					else
					{
						$this->db->set($key, $value['DATA'], FALSE);
					}
				}
			}
			
			$this->db->where($where);
			$result = $this->db->update($table); 

			if ($this->db->trans_status() === FALSE){$this->db->trans_rollback();}
			else{$this->db->trans_commit();}       

		} catch (Exception $e) {
			$this->session->set_flashdata($e);
		}

		return $result;
	}



	protected function getQuery($table, $selection = null, $filter = null, $sort = null, $joins = null, $group_by = null, $limit = null){
      //NOTES:
      //FORMAT $selection : "field_a, field_b, field_c,...."
      //FORMAT $filter : array(0 => array(0 => "SPECIFICATION", 1 = > "FIELD", 2 => "VALUE"))
      //FORMAT $sort : array(0 => array(0 => "FIELD", 1 = > "ASC / DESC"))
      //FORMAT $joins : array(0 => array("join_table" => "value", "join_on" => "value", "join_heading" => "value"))

		$select = "*";
		if($selection != null ){
			$select = $selection;
		}
		$this->db->select($select);
		$this->db->from($table);
		if($filter != null){
			$this->generateWhereFromFilter($filter);
		}
		if($sort != null){
			
			$this->generateSorting($sort);
		}
		if($joins != null){
			$this->generateJoin($joins);
		}
		if($group_by != null){
			$this->db->group_by($group_by);
		}
		if($limit != null ){
			$this->db->limit($limit);
		}

		$this->checkInput($this->db->get_compiled_select());
		die();
	}

    // protected function update($table, $input, $where){
    //   try {
    //     // $input = $this->setAuditInformation($input, 2); 

    //     $this->db->trans_begin(); 
    //     $this->db->where($where);
    //     $this->db->update($table, $input); 

    //     if ($this->db->trans_status() === FALSE){$this->db->trans_rollback();}
    //     else{$this->db->trans_commit();} 

    //     return true;    
	
    //   } catch (Exception $e) {
    //     $this->session->set_flashdata($e);
    //     return false;
    //   }
    // }

    // protected function delete($table, $where_id){
    //   try {
    //     $this->db->trans_begin(); 
    //     $this->db->where($where_id);
    //     $this->db->delete($table); 

    //     if ($this->db->trans_status() === FALSE){$this->db->trans_rollback();}
    //     else{$this->db->trans_commit();} 

    //     return true;    
	
    //   } catch (Exception $e) {
    //     $this->session->set_flashdata($e);
    //     return false;
    //   }
    // }

    // PRIVATE SECTION
	private function generateJoin($joins){
		foreach ($joins as $value) {
			$this->db->join($value["join_table"], $value["join_on"], $value["join_heading"]);
		}
	}

	private function setAuditInformation($input, $audit_type){
      // NOTES:
      // type 1 = insert
      // type 2 = update

      // if($audit_type == 1){
      //   $input["torg"] = (isset($_SESSION["userinfo"]) && $_SESSION["userinfo"] != null ? $_SESSION["userinfo"]["id"] : "");
      //   $input["created_date"] = date("Y-m-d H:i:s");
      // }
      // else if($audit_type == 2){
      //   $input["user"] = (isset($_SESSION["userinfo"]) && $_SESSION["userinfo"] != null ? $_SESSION["userinfo"]["id"] : "");
      //   $input["updated_date"] = date("Y-m-d H:i:s");
      // }

      // return $input;

		return null;
	}

	private function generateWhereFromFilter($filters){
		foreach ($filters as $filter) {

			if($filter == null){continue;}

			if(strtoupper($filter[0]) == "LIKE"){
				$this->db->like($filter[1], $filter[2]);
				continue;
			}
			else if(strtoupper($filter[0]) == "EQUAL"){
				$this->db->where($filter[1], $filter[2]);
				continue;
			}
			else if(strtoupper($filter[0]) == "NOT_EQUAL"){
				$this->db->where($filter[1]." != ", $filter[2]);
				continue;
			}
			else if(strtoupper($filter[0]) == "LESS_OR_EQUAL"){
				$this->db->where($filter[1]." <= ", $filter[2]);
				continue;
			}
			else if(strtoupper($filter[0]) == "GREATER_OR_EQUAL"){
				$this->db->where($filter[1]." >= ", $filter[2]);
				continue;
			}
			else if(strtoupper($filter[0]) == "IN"){
				$this->db->where_in($filter[1], $filter[2]);
				continue;
			}
			else if(strtoupper($filter[0]) == "NOT_IN"){
	          $this->db->where_not_in($filter[1], $filter[2]);
	          continue;
	        }
	        else if(strtoupper($filter[0]) == "IS_NULL"){
	          $this->db->where($filter[1]." IS NULL", null, false);
	          continue;
	        }
	        else if(strtoupper($filter[0]) == "IS_NOT_NULL"){
	          $this->db->where($filter[1]." IS NOT NULL", null, false);
	          continue;
	        }
	        else if(strtoupper($filter[0]) == "WHERE_MANUAL"){
	          $this->db->where($filter[1], null, false);
	          continue;
	        }
		}
	}

	private function generateSorting($sort){
		foreach ($sort as $data) {
			if($data == null){continue;}
			$this->db->order_by($data[0], $data[1]);
		}
	}



	protected function julianToGregorian($julian){
		$data['isposted'] = true;
		
        // $julian=$this->input->post('julian');
		$julian2 = $julian + 1900000;
		$year = substr($julian2,0,4);
		$totmonth = substr($julian2,4,3);

		$listmonth=[31,28,31,30,31,30,31,31,30,31,30,31];

		if($year%4==0)
		{
			$listmonth[1]=29;
		}
		$month=0;
		$day=0;
		for($i=0;$i<12;$i++)
		{
			$month++;
			
			if($totmonth - $listmonth[$i] <= 0)
			{
				$day=(int)$totmonth;
				break;
			}
			$totmonth = $totmonth-$listmonth[$i];
		}
		$data['year']=$year;
		$data['month']=$month;
		$data['day']=$day;
		$data['full_date'] = $day."-".$month."-".$year;
		
		return $data['full_date'];
	}


	protected function gregoriantoJulian($gregorian){
		$date=date('d',strtotime($gregorian));
		$month=date('m',strtotime($gregorian));
		$year=date('Y',strtotime($gregorian));
		
		$julian =($year*1000) - 1900000;
		$day=mktime(0,0,0,$month,$date,$year);
		$day2=date("z",$day);
		$julian= $julian + $day2;
		$julian2=$julian+1;


		return $julian2;
	}


	private function getCurrentNumber($seq){
		$sql="SELECT GEN_NUMBER 
		FROM ".LEGACY.".GEN_LOOKUP_NUMB 
		WHERE GEN_APPS='".APPS_CODE."' AND GEN_SEQ=".$seq." AND GEN_FISCAL_YEAR=".date("y");
		return $this->db->query($sql)->result();      
	}

    //1. update  next number
	private function updateNextNumber($seq){
		$sql="UPDATE ".LEGACY.".GEN_LOOKUP_NUMB 
		SET GEN_NUMBER=(GEN_NUMBER + 1) 
		WHERE GEN_APPS='".APPS_CODE."' AND GEN_SEQ=".$seq." AND GEN_FISCAL_YEAR=".date("y");  
		$this->db->query($sql);
		return $this->db->affected_rows();      
	}

    //1. insert  next number
	private function insertNextNumber($seq, $text){
		try {
			$first_number = (date("y")) * FIRST_COUNTER_DB;
			
			$preparedData["GEN_APPS"]       = APPS_CODE;
			$preparedData["GEN_SEQ"]      = $seq;
			$preparedData["GEN_DESCRIPTION"]  = $text;
			$preparedData["GEN_FISCAL_YEAR"]  = date("y");
			$preparedData["GEN_NUMBER"]     = $first_number;

			$this->db->trans_start(FALSE);
			$this->db->insert(LEGACY.".GEN_LOOKUP_NUMB", $preparedData);
			$this->db->trans_complete();
			
			$db_error = $this->db->error();
			if (!empty($db_error)) {
				throw new Exception('Database error! Error Code [' . $db_error['code'] . '] Error: ' . $db_error['message']);
            return false; // unreachable retrun statement !!!
        }
        return TRUE;
    } 
    catch (Exception $e) {
          // this will not catch DB related errors. But it will include them, because this is more general. 
    	log_message('error: ',$e->getMessage());
    	return false;
    }    
}

public function affected_rows()
{
	return $this->db->affected_rows();
}

public function get($cond = '')
{
	if (is_array($cond))
		$this->db->where($cond);
	if (is_string($cond) && strlen($cond) > 3)
		$this->db->where($cond);

	$query = $this->db->get($this->data['table_name']);

	return $query->result();
}

public function select($fields = ['*'], $cond = '')
{
	$this->db->select($fields);
	$this->db->from($this->data['table_name']);
	if (is_array($cond))
		$this->db->where($cond);
	if (is_string($cond) && strlen($cond) > 3)
		$this->db->where($cond);
	
	$query = $this->db->get();

	return $query->result();
}

public function select_row($fields = ['*'], $cond = '')
{
	$this->db->select($fields);
	$this->db->from($this->data['table_name']);
	if (is_array($cond))
		$this->db->where($cond);
	if (is_string($cond) && strlen($cond) > 3)
		$this->db->where($cond);
	
	$query = $this->db->get();

	return $query->row();
}

public function get_by_order($ref, $order, $cond = '')
{
	if (is_array($cond))
		$this->db->where($cond);
	if (is_string($cond) && strlen($cond) > 3)
		$this->db->where($cond);

	$this->db->order_by($ref, $order);
	$query = $this->db->get($this->data['table_name']);

	return $query->result();
}

public function get_last_row($cond = '', $order_by = null)
{
	if (is_array($cond))
		$this->db->where($cond);
	if (is_string($cond) && strlen($cond) > 3)
		$this->db->where($cond);

	if ($order_by != null)
		$this->db->order_by($order_by, 'DESC');
	
	$this->db->order_by($this->data['primary_key'], 'DESC');
	$this->db->limit(1);
	$query = $this->db->get($this->data['table_name']);

	return $query->row();
}	

public function get_by_order_limit($ref, $order, $cond = '')
{
	if (is_array($cond))
		$this->db->where($cond);

	$this->db->order_by($ref, $order);
	$this->db->limit(1);
	$query = $this->db->get($this->data['table_name']);

	return $query->row();
}

public function get_by_order_any_limit($ref, $order, $number, $cond = '')
{
	if (is_array($cond))
		$this->db->where($cond);

	$this->db->order_by($ref, $order);
	$this->db->limit($number);
	$query = $this->db->get($this->data['table_name']);

	return $query->result();
}

public function get_row($cond)
{
	$this->db->where($cond);
	$query = $this->db->get($this->data['table_name']);

	return $query->row_array();
}

public function insert($data)
{
	return $this->db->insert($this->data['table_name'], $data);
}

public function insertOracle($data, $constantSeq, $constantDesc, $extra = NULL)
{
	$data["ID"] 	= self::getNextNumber($constantSeq, $constantDesc);

	foreach ($data as $key => $value)
	{
		$this->db->set($key, $value);
	}

	if ($extra != NULL)
	{
		foreach ($extra as $key => $value)
		{
			if ($value['TYPE'] == 'DATE')
			{
				$this->db->set($key, "TO_DATE('" . $value['DATA'] . "', 'yyyy/mm/dd HH24:MI:SS')", FALSE);
			}
			else
			{
				$this->db->set($key, $value['DATA'], FALSE);
			}
		}
	}
	$this->db->insert($this->data['table_name']);
	return $data['ID'];
}

public function insertOracleRaw($data, $constantSeq, $constantDesc, $extra = NULL)
{
	$data['ID']   = self::getNextNumber($constantSeq, $constantDesc);
	
	$values = [];
	$keys   = [];

	foreach ($data as $key => $value)
	{
		$values []= "'" . $value . "'";
		$keys []= $key;
	}

	if ($extra != NULL)
	{
		foreach ($extra as $key => $value)
		{
			$keys []= $key;
			if ($value['TYPE'] == 'DATE')
			{
				$values []= "TO_DATE('" . $value['DATA'] . "', 'YYYY/MM/DD HH24:MI:SS')";
			}
			else
			{
				$values []= "'" . $value['DATA'] . "'";
			}
		}
	}

	$sql = 'INSERT INTO ' . $this->data['table_name'] . ' (' . implode(',', $keys) . ') VALUES (';

	$sql .= implode(',', $values);

	$sql .= ')';
	$this->db->query($sql);
	return $data['ID'];
}

public function update($pk, $data)
{
	$this->db->where($this->data['primary_key'], $pk);
	return $this->db->update($this->data['table_name'], $data);
}

public function update_where($cond, $data)
{
	$this->db->where($cond);
	return $this->db->update($this->data['table_name'], $data);
}

public function delete($pk)
{
	$this->db->where($this->data['primary_key'], $pk);
	return $this->db->delete($this->data['table_name']);
}

public function delete_by($cond)
{
	$this->db->where($cond);
	return $this->db->delete($this->data['table_name']);
}

public function getOrdered($order = 'ASC')
{
	$query = $this->db->query('SELECT * FROM ' . $this->data['table_name'] . ' ORDER BY ' . $this->data['primary_key'] . ' ' . $order);
	return $query->result();
}

public function getDataLike($like)
{
	$this->db->select('*');
	$this->db->like($like);
	$query = $this->db->get($this->data['table_name']);
	return $query->result();
}

public function getDataJoin($tables, $jcond)
{
	$this->db->select('*');
	for ($i = 0; $i < count($tables); $i++)
		$this->db->join($tables[$i], $jcond[$i]);
	return $this->db->get($this->data['table_name'])->result();
}

public function getJSON($url)
{
	$content = file_get_contents($url);
	$data = json_decode($content);
	return $data;
}

public function validate($conf)
{
	$this->load->library('form_validation');
	$this->form_validation->set_rules($conf);
	return $this->form_validation->run();
}

public function required_input($input_names)
{
	$rules = [];
	foreach ($input_names as $input)
	{
		$rules []= [
			'field'		=> $input,
			'label'		=> ucfirst($input),
			'rules'		=> 'required'
		];
	}

	return $this->validate($rules);
}

public function flashmsg($msg, $type = 'success',$name='msg')
{
	return $this->session->set_flashdata($name, '<div class="alert alert-'.$type.' alert-dismissable"> <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>'.$msg.'</div>');
}

public function get_col($col)
{
	$query = $this->db->query('SELECT '.$col.' FROM ' . $this->data['table_name']);
	return $query->result();
}

}
