<?php
class DBi {
    public static $mysqli;
	public static $skip_error;
	public static $query_log_file;
	private static $result;
	private static $second_mysqli;
	
	public static function prepare(){
		$args = func_get_args();
		$query = array_shift($args);

		$idx=0;
		$offset=0;
		while($offset < mb_strlen($query) && ($pos = mb_strpos($query, '?', $offset)) !== FALSE){
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
						foreach($val as $k=>$v) if(in_array(gettype($v),['string','NULL'])) $val[$k] = "'".self::$mysqli->real_escape_string($v)."'";
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
			
			$repl_len = in_array($mod,['a','#']) ? 2 : 1;
			$query = self::mb_substr_replace($query, $val, $pos, $repl_len);
			$offset = $pos + mb_strlen($val);
			$idx++;
		}
		
		return $query;
	}
	
	public static function init(){
		if(!self::$mysqli){
			self::$mysqli = new mysqli(dbhost, dbuser, dbpass, dbname);
			if(self::$mysqli->connect_error) die('Ошибка подключения ('.self::$mysqli->connect_errno.') '.self::$mysqli->connect_error);
		}
	}
	
	public static function query(){
		self::init();
		$query = call_user_func_array(array(__CLASS__, 'prepare'), func_get_args());	
		
		//логгирование запросов
		if(self::$query_log_file) file_put_contents(self::$query_log_file, $query."\n", FILE_APPEND);

		if(!empty(self::$result->field_count)){ //если есть незавершенная выборка - выполним запрос в новом соединении
			if(!self::$second_mysqli) self::$second_mysqli = new mysqli(dbhost, dbuser, dbpass, dbname); 
			$result = self::$second_mysqli->query($query, MYSQLI_USE_RESULT);
			return self::check_result($result);
		}else{ //обычная выборка
			self::$result = self::$mysqli->query($query, MYSQLI_USE_RESULT);
		}
		
		return self::check_result(self::$result);
	}
	
	public static function query_cnt(){
		$result = call_user_func_array(array(__CLASS__, 'query'), func_get_args());
		return self::$mysqli->affected_rows ?: 0;
	}
	
	private static function check_result($result){
		if(!$result && !self::$skip_error){
			echo("\nSQL Error: ".(self::$mysqli->error?:self::$second_mysqli->error)."\n"); 
			debug_print_backtrace(); 
			die;
		} else 
			return $result;
	}
	
	public static function select(){
		$res = [];
		$rows = call_user_func_array(array(__CLASS__, 'query'), func_get_args());
		if(!$rows) return $res;
		while($row = $rows->fetch_assoc()) $res[]=$row;
		$rows->free();
		return $res;
	}
	public static function selectCol(){
		$res = [];
		$rows = call_user_func_array(array(__CLASS__, 'query'), func_get_args());
		if(!$rows) return $res;
		while($row = $rows->fetch_assoc()) $res[] = array_shift($row);
		$rows->free();
		return $res;
	}
	public static function selectRow(){
		$rows = call_user_func_array(array(__CLASS__, 'query'), func_get_args());
		if(!$rows) return [];
		$row = $rows->fetch_assoc();
		$rows->free();
		return $row;
	}
	public static function selectCell(){
		$rows = call_user_func_array(array(__CLASS__, 'query'), func_get_args());
		if(!$rows) return $res;
		$row = $rows->fetch_assoc();
		$res = !empty($row) ? array_shift($row) : null;
		$rows->free();
		return $res;
	}
	
	public static function transaction(){
		self::init();
		self::$mysqli->begin_transaction();
	}
	public static function commit(){
		self::$mysqli->commit();
	}
	public static function rollback(){
		self::$mysqli->rollback();
	}
	
	private static function escape($string){
		$return = '';
		for($i = 0; $i < strlen($string); ++$i) {
			$char = $string[$i];
			$ord = ord($char);
			if($char !== "'" && $char !== "\"" && $char !== '\\' && $ord >= 32 && $ord <= 126)
				$return .= $char;
			else
				$return .= '\\x' . dechex($ord);
		}
		return $return;
	}
	
	private static function mb_substr_replace($original, $replacement, $position, $length){
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