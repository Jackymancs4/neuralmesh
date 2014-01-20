<?php
//
// In your Model class, you must initialize it by calling mysql::init()
//
class mysql {
	private static $db;
	private static $queries;
	
	public static function init() {
		self::$db = new mysqli(DB_HOST,DB_USER,DB_PASS); //edit in nm-settings.php
		self::$queries = array();
    
		    if(!$selezione = self::$db->select_db(DB_NAME))
        {
		      $q = self::$db->query("CREATE DATABASE ".DB_NAME) or die (mysql_error());
          $selezione = self::$db->select_db(DB_NAME);
        }
        
   return true;
         
	}

	public static function query($name, $parameters = array(), $force=false) {
		$meta = explode(".",$name);
		
		//Load from XML if query doesn't exist
		if(!isset(self::$queries[$name])) {
			$queriesXML = simplexml_load_file(Controller::$root."data/".$meta[0].".xml");
			foreach($queriesXML as $query) {
				$key = $meta[0].".".(string)$query['name'];
				self::$queries[$key] = (string)$query;
			}
		}
		
		$sql = self::$queries[$name] or die("Error: query <var>$name</var> is not found!");
		
		if (count($parameters)) {
			$formattedParams = array();
			// Prepend a ':' to each parameter name and escape the value properly.
			foreach($parameters as $paramName => $paramValue) {				
				if(!$force) {
					switch($paramValue) {
						case NULL:
							$paramValue = "NULL";
							break;
						case "DEFAULT": break;
						default:
							$paramValue = "'".self::$db->real_escape_string($paramValue)."'";
							break;
					}
				}
				$formattedParams[":$paramName"] = $paramValue;
			}
			// Replace placeholders in the query with assigned values.
			$sql = strtr($sql, $formattedParams);
		}		
		// Execute the query and return the result.
		$q = self::$db->query($sql) or die("<b>Database error:</b> ".$sql);
		return $q;
	}
	
	public static function rawquery($sql) {
		return self::$db->query($sql);
	}
	
	public static function last_id() {
		return self::$db->insert_id;
	}
	
	/**
	 * Replacement for lack of fetch_all
	 * @param q MySQLi query object
	 * @return multidimensional associative array of the query
	 */
	public static function fetch_all($q) {
		$data = array();
		while($row = $q->fetch_assoc()) {
			$data[] = $row;
		}
		return $data;
	}


  public static function import_sql_file($location){
    
    //load file
    $commands = file_get_contents($location);

    //delete comments
    
    $lines = explode("\n",$commands);
    $commands = '';
    foreach($lines as $line){
        $line = trim($line);
        if( $line && !(substr($line, 0, strlen('--')) == '--') ){
            $commands .= $line . "\n";
        }
    }

    //convert to array
    $commands = explode(";", $commands);

    //run commands
    $total = $success = 0;
    foreach($commands as $command){
        if(trim($command)){
            $success += (self::$db->query($command)==false ? 0 : 1);
            $total += 1;
        }
    }

    //return number of successful queries and total number of queries found
    return array(
        "success" => $success,
        "total" => $total
    );
  }
}
?>