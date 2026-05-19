<?php 
class db {
	public function e($str){
		return addslashes($str);
	}
	public function insert($table, $data=array()){
		global $pdo;
		$insert_id = false;
		if($table!="" && is_array($data) && !empty($data)){
			$insert_str = "insert into `".$table."` ";
			$fields = ""; $values = ""; $vlArray = array();
			foreach($data as $key=>$value){
				$fields .= ($fields!=""?", ":"")."`$key`";
				$values .= ($values!=""?", ":"")."?";
				$vlArray[] = $value;
			}
			$insert_str .= "($fields) values ($values)";
			$qry = $pdo->prepare($insert_str);
			// var_dump($insert_str, $vlArray);
			$insert_id = $qry->execute($vlArray)?$pdo->lastInsertId():false;
		}
		return $insert_id;
	}
	public function update($table, $conditions=array(), $data=array()){
		global $pdo;
		$return = false;
		if($table!="" && is_array($data) && !empty($data) && is_array($conditions) && !empty($conditions)){
			$update_str = "update `".$table."` set ";
			$update_datas=""; $preValues=array();
			foreach($data as $key=>$value){
				$update_datas .= ($update_datas!=""?", ":"")."`$key`=?";
				$preValues[] = $value;
			}
			$update_str .= $update_datas;
			$condition_datas = array();
			if(isset($conditions["#cus"])){
				!empty($conditions["#cus"])?$condition_datas[]=$conditions["#cus"]:0;
				unset($conditions["#cus"]);
			}
			foreach($conditions as $key=>$value){
				$condition_datas[] = "`$key`=?";
				$preValues[] = $value;
			}
			$update_str .= " where ".implode(" and ", $condition_datas);
			$return = $pdo->prepare($update_str)->execute($preValues);
			// var_dump($update_str, $preValues);
		}
		return $return;
	}
	public function delete($table, $conditions=array()){
		global $pdo;
		$return = false;
		if($table!="" && is_array($conditions)){
			$delete_str = "delete from `".$table."`";
			$condValues = array();
			if(!empty($conditions)){
				$condition_datas = array();
				if(isset($conditions["#cus"])){
					if($conditions["#cus"]!=""){
						$condition_datas[] = $conditions["#cus"];
					}
					unset($conditions["#cus"]);
				}
				foreach($conditions as $key=>$value){
					$condition_datas[] = "`$key`=?";
					$condValues[] = $value;
				}
				$delete_str .= " where ".implode(" and ", $condition_datas);
			}
			$return = $pdo->prepare($delete_str)->execute($condValues);
		}
		return $return;
	}
	public function get($table, $conditions=array(), $display_fields="*"){
		global $pdo;
		$response = new stdClass();
		$response->data = array();
		$all_result = isset($conditions["#all"]);
		if($all_result){ unset($conditions["#all"]); }
		if($table!="" && is_array($conditions)){
			if(gettype($display_fields)=="string" && preg_match("/(\[|\])/", $display_fields)){
				preg_match_all("/\[.+?\]/", $display_fields, $matches);
				if(isset($matches[0])){
					foreach($matches[0] as $match){
						$display_fields = str_replace($match, (preg_replace("/\./", "`.`", str_replace("=", "` as `", $match))), $display_fields);
					}
				}
				$display_fields = preg_replace("/\|/", "`, `", $display_fields);
				$display_fields = preg_replace("/(\[|\])/", "`", $display_fields);
			}
			if(preg_match("/^\[.*\]$/", $table)){
				$table = preg_replace("/([a-zA-Z\_0-9]{1,})\=/", "$1=", $table);
				$table = preg_replace("/[\[\]]/", "`", $table);
				$table = preg_replace("/\|/", "`, `", $table);
				$table = preg_replace("/\=/", "` `", $table);
			}else{
				$table = "`$table`";
			}
			$select_str = "select ".($display_fields=="*"?"*":
			(is_array($display_fields)?("`".implode("`, `", $display_fields)."`"):$display_fields))." from $table";
			$show_query = false; $condition_str = ""; $grouping_str = ""; $sorting_str = ""; $limit_str = ""; $join_str = ""; $fetch_str=PDO::FETCH_OBJ; $selVals = array();
			if(count($conditions)>0){
				$qry_conditions = array();
				$qry_conditions_secondary = array();
				
				if(isset($conditions["#cus"])){
					if($conditions["#cus"]!=""){
						$qry_conditions[] = $conditions["#cus"];
					}
					unset($conditions["#cus"]);
				}
				if(isset($conditions["#join"])){
					$join_str = $conditions["#join"];
					unset($conditions["#join"]);
				}
				if(isset($conditions["#show"])){
					$show_query = true;
					unset($conditions["#show"]);
				}
				if(isset($conditions["#group"]) && $conditions["#group"]!=""){
					$grouping_str = ' group by `'.$conditions["#group"].'`';
					unset($conditions["#group"]);
				}
				if(isset($conditions["#srt"]) && $conditions["#srt"]!=""){
					$sorting_str = ' order by '.$conditions["#srt"];
					unset($conditions["#srt"]);
				}
				if(isset($conditions["#limit"]) && is_numeric($conditions["#limit"])){
					$limit = intval($conditions["#limit"]);
					$limit = $limit<1?1:$limit;
					$page_no = 0;
					if(isset($conditions["#page"]) && is_numeric($conditions["#page"])){
						$page_no = intval($conditions["#page"]);
						$page_no = $limit<0?0:$page_no;
						$response->current = $page_no;
						$page_no = $page_no * $limit;
					}
					$limit_str = " limit $page_no, $limit";
					unset($conditions["#page"], $conditions["#page"]);
					unset($conditions["#limit"], $conditions["#limit"]);
				}
				if(isset($conditions["#fetch"]) && $conditions["#fetch"]!=""){

					switch ($conditions["#fetch"]) {
					  case 'column':
					    $fetch_str = PDO::FETCH_COLUMN;
					    break;
					  case 'group':
					    $fetch_str = PDO::FETCH_GROUP;
					    break;
					  case 'column-group':
					    $fetch_str = PDO::FETCH_COLUMN|PDO::FETCH_GROUP;
					    break;
					  case 'unique':
					    $fetch_str = PDO::FETCH_UNIQUE;
					    break;
					  default:
					   $fetch_str = PDO::FETCH_OBJ;
					}
					unset($conditions["#fetch"]);
				}
				foreach($conditions as $key=>$value){
					$key = preg_replace("/\./", "`.`", $key);
					if($value===null){
						$key="ifnull(`$key`, 'NULL')"; $value="NULL";
					}else{
						$key="`$key`";
					}
					$qry_conditions[] = "$key=?";
					$selVals[] = $value;
				}
				$qry_conditions = array_merge($qry_conditions, $qry_conditions_secondary);
				$condition_str .= (count($qry_conditions)>0?" where ":"").implode(" and ", $qry_conditions);
			}
			$qry_string = $select_str.$join_str.$condition_str.$grouping_str.$sorting_str.$limit_str;
			$qry_pdo = $pdo->prepare($qry_string);
			
			if($qry_pdo->execute($selVals)){
				$response->data = $qry_pdo->fetchAll($fetch_str);
				if($limit_str!=""){
					if($grouping_str==""){
						$page_num_check_str = "select count(1) as `pages` from $table".$join_str.$condition_str;
						$page_num_check = $pdo->prepare($page_num_check_str);
						$response->pages = 0;
						if($page_num_check->execute($selVals)){
							$page_num_result = $page_num_check->fetch(PDO::FETCH_OBJ)->pages;
							$page_limit_num = intval(preg_replace("/(.+?), /i", "", $limit_str));
							if($page_num_result>0 && $page_limit_num>0){
								$response->total = $page_num_result;
								$response->pages = ceil($page_num_result/$page_limit_num);
							}
						}
					}else{
						$page_num_check_str = "select 1 as `pages` from $table".$join_str.$condition_str.$grouping_str;
						$page_num_check = $pdo->prepare($page_num_check_str);
						$response->pages = 0;
						if($page_num_check->execute($selVals)){
							$results = $page_num_check->rowCount();
							$page_limit_num = intval(preg_replace("/(.+?), /i", "", $limit_str));
							$response->total = $results;
							$response->pages = ceil($results/$page_limit_num);
						}
					}
				}
			}else{
				$response->error = $qry_pdo->errorInfo();
				$response->error = isset($response->error[2])?$response->error[2]:"";
			}
		}
		if($show_query){
			$response->query = $qry_string;
			foreach($selVals as $sel){
				$response->query = preg_replace('/[?]/', $sel, $response->query, 1);
			}
		}
		if($all_result){
			$response = $response;
		}else{
			$dta = isset($response->data[0])?$response->data[0]:false;
			$show_query?$response->data = $dta:$response =$dta; 
		}	
		
		return $response;
	}
	public function custom($query_string, $all=false){
		global $pdo; 
		$data = $pdo->prepare($query_string); 
		$er = $data->execute();
		$response = new stdClass(); $response->data = array();
		$response->data = $all?$data->fetchAll(PDO::FETCH_OBJ):$data->fetch(PDO::FETCH_OBJ);
		if($er==false){
			$response->error = $data->errorInfo();
			$response->error = isset($response->error[2])?$response->error[2]:"";
		}
		return $response;
	}
	public function new_slug($table, $field, $name, $id=false){
		global $site;
		$slug = "";
		$name = $site->str2url($name);
		$ind = 0;
		while($slug=="" || $this->get("$table", array("$field"=>$slug, "#cus"=>$id!==false?"id!=$id":""), "id")!=false){
			$slug = $name.($ind>0?"-$ind":"");
			$ind++;
		}
		return $slug;
	}
	public function options($fields = array()){
		global $site;
		$cond = array('#all'=>1);
		!empty($fields)?$cond['#cus']= '`key1` in ("'.(implode('", "', $fields)).'")':'';
		$getdata = $this->get('settings', $cond);
		$options = new stdClass;
		foreach($getdata->data as $dta){
			$fieldName = $dta->key1;
			$options->$fieldName = $dta->value;
		}
		return $options;
	}
}
$db = new db();
?>