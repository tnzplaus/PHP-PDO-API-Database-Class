<?php
class PDODb
{
	private $dbLink;
	private $dbStmt;
	private $dbHost;
	private $dbUsername;
   	private $dbPassword;
	private $dbName;

	private $table;
	private $field;
	private $from;
	private $on;
	private $where;
	private $group;
	private $order;
	private $limit;
	private $bind_array;
	private $type;
	
	public  $queryCount;
	public  $printQuery;
	
	function PDODb($dbHost,$dbUsername,$dbPassword,$dbName)
	{
		$this->dbHost = $dbHost;
		$this->dbUsername = $dbUsername;
		$this->dbPassword = $dbPassword;
		$this->dbName = $dbName;	
		$this->queryCount = 0;
		$this->printQuery = false;
	}
	function destruct()
	{
		$this->close();
	}
	private function connect() {	
		try {
			$this->dbLink = new PDO("mysql:host=$this->dbHost;dbname=$this->dbName;charset=utf8", $this->dbUsername, $this->dbPassword);
		} catch(PDOException $e) {
			echo 'ERROR: ' . $e->getMessage();
			exit;
		}
		unset ($this->dbHost, $this->dbUsername, $this->dbPassword, $this->dbName);		
	}
	private function close() {	
		$this->dbLink = null;
		$this->dbStmt = null;
	}
	private function assign($query_array)
	{
		if (array_key_exists('table', $query_array))
			$this->table = ($query_array['table']) ? $query_array['table'] : '';
		else
			$this->table = '';
		if (array_key_exists('value', $query_array))
			$this->value = ($query_array['value']) ? $query_array['value'] : '';
		else
			$this->value = '';

		if (array_key_exists('field', $query_array))
			$this->field = ($query_array['field']) ? $query_array['field'] : '';
		else
			$this->field = '';
		if (array_key_exists('from', $query_array))
			$this->from = ($query_array['from']) ? $query_array['from'] : '';
		else
			$this->from = '';
		if (array_key_exists('on', $query_array))
			$this->on = ($query_array['on']) ? 'ON '.$query_array['on'] : '';
		else
			$this->on = '';
		if (array_key_exists('where', $query_array))
			$this->where = ($query_array['where']) ? 'WHERE '.$query_array['where'] : '';
		else
			$this->where = '';
		if (array_key_exists('group', $query_array))
			$this->group = ($query_array['group']) ? 'GROUP BY '.$query_array['group'] : '';
		else
			$this->group = '';
		if (array_key_exists('order', $query_array))
			$this->order = ($query_array['order']) ? 'ORDER BY '.$query_array['order'] : '';
		else
			$this->order = '';
		if (array_key_exists('limit', $query_array))
			$this->limit = ($query_array['limit']) ? 'LIMIT '.$query_array['limit'] : '';
		else
			$this->limit = '';
		$this->bind_array = array();
		if (array_key_exists('bind', $query_array))
		{
			foreach($query_array['bind'] as $key => $value)
			{
				$this->bind_array[$key] =  $value;
			}
		}
	}
	private function print_query($query){
		echo "Query:<br/>".$query.'<br/><br/>Bind Array:<br/>';
		print_r($this->bind_array);
		echo '<br/><br/>';
		$this->printQuery = false;
	}
	public function setPrintQuery($value){
		$this->printQuery = $value;
	}
	private function execQuery($query)
	{
		if($this->printQuery)
			$this->print_query($query);
		$this->dbStmt = $this->dbLink->prepare($query);
		$result = $this->dbStmt->execute($this->bind_array);
		$this->queryCount++;
		if($this->type == "select")
		{
			$result = $this->dbStmt->fetchAll(PDO::FETCH_ASSOC);
		}
		else if($this->type == "insert")
			$result = $this->dbLink->lastInsertId();
		else if($this->type == "exists")
		{
			$data = $this->dbStmt->fetchAll(PDO::FETCH_ASSOC);
			$result = $data[0]['found'];
		}
		return $result;
	}
	function select($query_array) {
		if(!$this->dbLink)
			$this->connect();
		$this->type = 'select';
		$this->assign($query_array);
		$query = "SELECT $this->field From $this->from $this->on $this->where $this->group $this->order $this->limit";
		return $this->execQuery($query);
	}
	function update($query_array) {
		if(!$this->dbLink)
			$this->connect();
		$this->type = 'update';
		$this->assign($query_array);
		$fields = array();
		foreach($this->field as $value)
			$fields[] = $value.' = :'.$value;
		$field = implode(', ', $fields);
		$query = "UPDATE $this->table SET $field $this->where";
		return $this->execQuery($query);
	}
	function insert($query_array)
	{
		if(!$this->dbLink)
			$this->connect();	
		$this->type = 'insert';		
		$this->assign($query_array);
		$field = '(`'.implode('`, `', $this->field).'`)';
		$count = 1;
		$value_str = '';
		foreach($this->bind_array as $key => $value)
		{
			$value_str .= ':'.$key;
			if($count % count($this->field) == 0)
			{
				$values[] = $value_str;
				$value_str = '';
			}
			else
				$value_str .=  ', ';
			$count++;
		}
		$value = '('.implode('), (', $values).')';
		$query = "INSERT INTO $this->table $field VALUES $value";
		return $this->execQuery($query);
	}
	function delete($query_array) {
		if(!$this->dbLink)
			$this->connect();
		$this->type = 'delete';
		$this->assign($query_array);
		$query = "DELETE From $this->from $this->where";
		return $this->execQuery($query);
	}
	function alter($query_array) {
		if(!$this->dbLink)
			$this->connect();
		$this->type = 'alter';
		$this->assign($query_array);
		$query = "ALTER TABLE $this->table $this->field";
		return $this->execQuery($query);
	}
	function exists($query_array)
	{
		if(!$this->dbLink)
			$this->connect();
		$this->type = 'exists';
		$this->assign($query_array);
		$value_str = '';
		$count = 0;
		foreach($this->bind_array as $key => $value)
		{
			$value_str .= ':'.$key;
			if($count % count($this->field) == 0)
			{
				$values[] = $value_str;
				$value_str = '';
			}
			else
				$value_str .=  ', ';
			$count++;
		}
		$value = implode(', ', $values);
		$query = "SELECT EXISTS (SELECT 1 FROM $this->from $this->where) as found";
		return $this->execQuery($query);
	}
}
?>
