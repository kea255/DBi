<?php
class DBi {
    public static $mysqli;
	
	public function prepare(){
		$args = func_get_args();
		$query = array_shift($args);

		$idx=0;
		while(($pos = mb_strpos($query, '?')) !== FALSE){
			$mod = mb_substr($query, $pos+1, 1);
			$val = $args[$idx];
			$type = gettype($val);
			if($type == 'boolean') $val=($val?'1':'0');
			else
			if($type == 'string'){
				$val = "'".self::$mysqli->real_escape_string($val)."'";
			}else
			if($type == 'array'){
				if($mod=='a'){
					if(self::is_assoc($val))
						foreach($val as $k=>$v) $val[$k] = "`$k`='".self::$mysqli->real_escape_string($v)."'";
					else
						foreach($val as $k=>$v) if(gettype($v)=='string') $val[$k] = "'".self::$mysqli->real_escape_string($v)."'";
					$val = implode(', ', array_values($val));
				}else
				if($mod=='#'){
					foreach($val as $k=>$v) $val[$k] = "`".self::$mysqli->real_escape_string($v)."`";
					$val = implode(', ', array_values($val));
				}else{
					die("Err: var type array but mod empty from query='$query'");
				}
			}else
			if($type == 'NULL'){
				$val = 'NULL';
			}else
			if(in_array($type,['double','integer','float'])){
				
			}else{
				die("Err: uncnown type var='$var', type='$type' from query='$query'");
			}			
			
			$query = self::mb_substr_replace($query, $val, $pos, in_array($mod,['a','#']) ? 2 : 1);
			$idx++;
		}
		
		return $query;
	}
	
	public function init(){
		if(!self::$mysqli) self::$mysqli = new mysqli(dbhost, dbuser, dbpass, dbname); 
		if(self::$mysqli->connect_error) die('Ошибка подключения ('.self::$mysqli->connect_errno.') '.self::$mysqli->connect_error);
	}
	
	public function query(){
		self::init();
		$query = call_user_func_array(array(self, 'prepare'), func_get_args());	
		$result = self::$mysqli->query($query, MYSQLI_USE_RESULT);
		if(!$result){ echo("\nSQL Error: ".self::$mysqli->error."\n"); debug_print_backtrace(); die;} else return $result;
	}
	
	public function select(){
		$res = [];
		$rows = call_user_func_array(array(self, 'query'), func_get_args());
		while($row = $rows->fetch_assoc()) $res[]=$row;
		$rows->free();
		return $res;
	}
	public function selectCol(){
		$res = [];
		$rows = call_user_func_array(array(self, 'query'), func_get_args());
		while($row = $rows->fetch_assoc()) $res[] = array_shift($row);
		$rows->free();
		return $res;
	}
	public function selectRow(){
		$rows = call_user_func_array(array(self, 'query'), func_get_args());
		$row = $rows->fetch_assoc();
		$rows->free();
		return $row;
	}
	public function selectCell(){
		$res = null;
		$rows = call_user_func_array(array(self, 'query'), func_get_args());
		$row = $rows->fetch_assoc();
		$res = array_shift($row);
		$rows->free();
		return $res;
	}
	
	public function transaction(){
		self::$mysqli->begin_transaction();
	}
	public function commit(){
		self::$mysqli->commit();
	}
	public function rollback(){
		self::$mysqli->rollback();
	}
	
	private function _pass_by_reference(&$arr){
		$refs = array();
		foreach($arr as $key => $value){
			$refs[$key] = &$arr[$key];
		}
		return $refs;
	}
	
	private function mb_substr_replace($original, $replacement, $position, $length){
		$startString = mb_substr($original, 0, $position, "UTF-8");
		$endString = mb_substr($original, $position + $length, mb_strlen($original), "UTF-8");
		$out = $startString . $replacement . $endString;
		return $out;
	}
	
	public static function is_assoc(array $array){
		$keys = array_keys($array);
		return array_keys($keys) !== $keys;
	}
}
?>