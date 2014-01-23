<?php
/**
 * @file plugin.mssql.php
 * @brief Contiene la classe mssql
 * 
 * @copyright 2013 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Libreria di connessione ai database SQL Server
 * 
 * @copyright 2013 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * ###GESTIONE CODIFICA UTF8
 * In SQL Server occorre gestire la codifica UTF8 dei dati.
 * 
 * ####Dal database alla visualizzazione
 * In questo caso si passa attraverso il metodo convertToHtml() richiamato dai metodi htmlChars, htmlCharsText, htmlInput, htmlInputEditor presenti nel file func.var.php.
 * 
 * ####Dal form al database
 * I dati passano attraverso il metodo convertToDatabase() (file func.var.php) richiamato direttamente dalle librerie di connessione al database.
 * 
 */
class mssql implements DbManager {

	private $_db_host, $_db_name, $_db_user, $_db_password, $_db_charset, $_dbconn;
	private $_sql;
	private $_qry;	// results of query
	private $_numberrows;
	private $_connection;
	private $_rows;
	private $_affected;
	private $_lastid;
	private $_dbresults = array();
	
	/**
	 * Costruttore
	 * 
	 * @param array $params parametri di connessione al database
	 *   - @b host (string): nome del server
	 *   - @b db_name (string): nome del database
	 *   - @b user (string): utente che si connette
	 *   - @b password (string): password dell'utente che si connette
	 *   - @b charset (string): encoding
	 *   - @b connect (boolean): attiva la connessione
	 * @return void
	 */
	function __construct($params) {
		
		$this->_db_host = $params["host"];
		$this->_db_name = $params["db_name"];
		$this->_db_user = $params["user"];
		$this->_db_password = $params["password"];
		$this->_db_charset = $params["charset"];
		
		$this->setnumberrows(0);
		$this->setconnection(false);
		
		if($params["connect"]===true) $this->openConnection();
	}
	
	/**
	 * Imposta la query come proprietà
	 * 
	 * @param string $sql_query query
	 */
	private function setsql($sql_query) {
		$this->_sql = $sql_query;
	}

	private function setnumberrows($numberresults) {
		$this->_numberrows = $numberresults;
	}
	
	private function setconnection($connection) {
		$this->_connection = $connection;
	}
	
	/**
	 * Esegue la query
	 * 
	 * @param string $query
	 * @return array
	 */
	private function execQuery($query=null) {
		
		if(!$query) $query = $this->_sql;
		
		$exec = mssql_query($query);
		return $exec;
	}
	
	/**
	 * @see DbManager::openConnection()
	 */
	public function openConnection() {

		if($this->_dbconn = mssql_connect($this->_db_host, $this->_db_user, $this->_db_password)) {
			
			@mssql_select_db($this->_db_name, $this->_dbconn) OR die("ERROR DB: ".mssql_get_last_message());
			$this->setconnection(true);
			return true;
		} else {
			die("ERROR DB: verify connection parameters");
		}
	}

	/**
	 * @see DbManager::closeConnection()
	 */
	public function closeConnection() {

		if($this->_connection){
			mssql_close($this->_dbconn);
		}
	}
	
	/**
	 * @see DbManager::begin()
	 */
	public function begin() {
		if (!$this->_connection){
			$this->openConnection();
		}
		$this->setsql("BEGIN");
		$this->_qry = mssql_query($this->_sql);
		if (!$this->_qry) {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * @see DbManager::rollback()
	 */
	public function rollback() {
		if (!$this->_connection) {
			$this->openConnection();
		}
		$this->setsql("ROLLBACK");
		$this->_qry = mssql_query($this->_sql);
		if (!$this->_qry) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Per tabelle innodb
	 * 
	 * @see DbManager::commit()
	 */
	public function commit() {
		if (!$this->_connection) {
			$this->openConnection();
		}
		$this->setsql("COMMIT");
		$this->_qry = mssql_query($this->_sql);
		if (!$this->qry) {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * @see DbManager::actionquery()
	 */
	public function actionquery($qry) {
		
		if (!$this->_connection) {
			$this->openConnection();
		}
		$this->setsql($qry);
		$this->_qry = mssql_query($this->_sql);
		
		return $this->_qry ? true:false;
	}

	/**
	 * @see DbManager::multiActionquery()
	 */
	public function multiActionquery($qry) {
	
		/*$conn = mysqli_connect($this->_db_host, $this->_db_user, $this->_db_password, $this->_db_name);
		$this->setsql($qry);
		$this->_qry = mysqli_multi_query($conn, $this->_sql);

		return $this->_qry ? true:false;*/
		return false;
	}

	/**
	 * @see DbManager::selectquery()
	 */
	public function selectquery($qry) {

		if(!$this->_connection) {
			$this->openConnection();
		}
		$this->setsql($qry);
		$this->_qry = mssql_query($this->_sql);
		if(!$this->_qry) {
			return false;
		} else {
			// initialize array results
			$this->_dbresults = array();
			
			$this->setnumberrows(mssql_num_rows($this->_qry));
			if($this->_numberrows > 0){
				while($this->_rows=mssql_fetch_assoc($this->_qry))
				{
					$this->_dbresults[]=$this->_rows;
				}
			}
			$this->freeresult();
			return $this->_dbresults;
		}
	}
	
	/**
	 * @see DbManager::freeresult()
	 */
	public function freeresult($res=null){
	
		if(is_null($res)) $res = $this->_qry;
		mssql_free_result($res);
	}
	
	/**
	 * @see DbManager::resultselect()
	 */
	public function resultselect($qry)
	{
		if(!$this->_connection) {
			$this->openConnection();
		}
		$this->setsql($qry);
		$this->_qry = mssql_query($this->_sql);
		if (!$this->_qry) {
			return false;
		} else {
			$this->setnumberrows(mssql_num_rows($this->_qry));
			return $this->_numberrows;
		}
	}
	
	/**
	 * @see DbManager::affected()
	 */
	public function affected() 
	{ 
		$this->_affected = mssql_rows_affected($this->_dbconn);
		return $this->_affected;
	}
	
	/**
	 * @see DbManager::getlastid()
	 */
	public function getlastid($table)
	{ 
		$id = 0;
    	$res = $this->execQuery("SELECT IDENT_CURRENT('$table') AS id");	// SCOPE_IDENTITY()
    	if($row = mssql_fetch_array($res, MSSQL_ASSOC)) { 
        	$id = $row["id"]; 
    	} 
    	$this->_lastid = $id;
		
		return $this->_lastid; 
	}
	
	/**
	 * Ottiene il valore del campo AUTO_INCREMENT
	 * 
	 * @see DbManager::autoIncValue()
	 */
	public function autoIncValue($table){

		$query = "SELECT IDENT_CURRENT('$table') AS NextId";
		$a = $this->selectquery($query);
		if($a && isset($a[0]))
		{
			$auto_increment = $a[0]['NextId'];
		}
		else $auto_increment = 0;
		
		$auto_increment++;
		
		return $auto_increment;
	}
	
	/**
	 * @see DbManager::getFieldFromId()
	 */
	public function getFieldFromId($table, $field, $field_id, $id) {
		
		$query = "SELECT $field FROM $table WHERE $field_id='$id'";
		$a = $this->selectquery($query);
		if(!$a){
			return '';
		}
		else
		{
			foreach($a as $b) {
				return $b[$field];
			}
		}
	}
	
	/**
	 * @see DbManager::tableexists()
	 */
	public function tableexists($table){
		
		$query = "SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='".DB_SCHEMA."' AND TABLE_TYPE='BASE TABLE' AND TABLE_NAME='$table'";
		$a = $this->selectquery($query);
		if($a)
			return true;
		else
			return false;
	}
	
	/**
	 * @see DbManager::fieldInformations()
	 */
	public function fieldInformations($table) {
	
		if($this->_connection) {
			$this->openConnection();
		}
		$this->setsql("SELECT TOP 1 * FROM ".$table);
		$this->_qry = mssql_query($this->_sql);
		if(!$this->_qry) {
			return false;
		} else {
			// initialize array results
			$meta = array();
			$i = 0;
			while($i < mssql_num_fields($this->_qry)) {
				$meta[$i] = mssql_fetch_field($this->_qry, $i);
				$meta[$i]->length = mssql_field_length($this->_qry, $i);
				$i++;
			}
			$this->freeresult();
			return $meta;
		}
	}
	
	/**
	 * @see DbManager::conformType()
	 * 
	 * @param string $type
	 * 
	 * Come tipo di dato di un campo, la funzione mssql_fetch_field() ritorna: int, char, text
	 */
	public function conformType($type) {
		
		
	}
	
	/**
	 * @see DbManager::limit()
	 * 
	 * Examples
	 * @code
	 * //Returning the first 100 rows from a table called employee:
	 * select top 100 * from employee
	 * //Returning the top 20% of rows from a table called employee:
	 * select top 20 percent * from employee 
	 * @endcode
	 */
	public function limit($range, $offset=0){
		
		$limit = "TOP $range";
		
		return $limit;
	}
	
	/**
	 * @see DbManager::distinct()
	 */
	public function distinct($fields, $options=array()) {
		
		$alias = gOpt('alias', $options, null);
		$remove_table = gOpt('remove_table', $options, true);
		
		if(!$fields) return null;
		
		if($remove_table)
		{
			$a_fields = explode(',', $fields);
			$a_data = array();
			foreach($a_fields AS $field)
			{
				$a_value = explode('.', trim($field));
				$a_data[] = $a_value[count($a_value)-1];
			}
			$fields = implode(', ', $a_data);
		}
		
		$data = "DISTINCT ($fields)";
		if($alias) $data .= " AS $alias";
		
		return $data;
	}
	
	/**
	 * @see DbManager::concat()
	 */
	public function concat($sequence){
		
		if(is_array($sequence))
		{
			if(sizeof($sequence) > 1)
			{
				/*$sequence2 = array();
				foreach($sequence AS $value)
				{
					if(is_int($value))
					{
						$len = strlen($value);
						$value = "CAST($value AS VARCHAR($len))";
					}
					
					$sequence2[] = $value;
				}*/
				$concat = implode(' + ', $sequence);
			}
			else $concat = $sequence[0];
		}
		else $concat = $sequence;
		
		return $concat;
	}

	/**
	 * @see DbManager::dumpDatabase()
	 */
	public function dumpDatabase($file) {

		$tables = $this->listTables();
		
		while ($td = mssql_fetch_array($tables)) {
			$table = $td[0];
			$r = mssql_query("SHOW CREATE TABLE $table");
			//SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='page_entry' ORDER BY ORDINAL_POSITION
			if ($r) {
				$insert_sql = "";
				$d = mssql_fetch_array($r);
				$d[1] .= ";";
				$SQL[] = str_replace("\n", "", $d[1]);
				
				$table_query = mssql_query("SELECT * FROM $table");
				$num_fields = mssql_num_fields($table_query);
				while ($fetch_row = mssql_fetch_array($table_query)) {
					$insert_sql .= "INSERT INTO $table VALUES(";
					for ($n=1;$n<=$num_fields;$n++) {
						$m = $n - 1;
						$insert_sql .= "'".$this->escapeString($fetch_row[$m]).($n==$num_fields ? "" : "', ");
					}
					$insert_sql .= ");\n";
				}
				if ($insert_sql!= "") {
					$SQL[] = $insert_sql;
				}
			}
		}

		if(!($fo = fopen($file, 'wb'))) return false;
		if(!fwrite($fo, implode("\r", $SQL))) return false;
		fclose($fo);

		return true;
	}
	
	private function listTables() {
		
		$query = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_CATALOG='".$this->_db_name."' AND TABLE_TYPE='BASE_TABLE'";
		$res = mssql_query($query);
		
		return $res;
	}
	
	/**
	 * @see DbManager::getTableStructure()
	 * @see getConstraintType()
	 * @see getCheckConstraint()
	 * @see getInformationKey()
	 */
	public function getTableStructure($table) {

		$structure = array("primary_key"=>null, "keys"=>array());
		$fields = array();

		$query = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_CATALOG='".$this->_db_name."' AND TABLE_NAME='$table'";
		$res = mssql_query($query);

		while($row = mssql_fetch_array($res)) {
			
			$column_name = $row['COLUMN_NAME'];
			
			$constraint_type = $this->getConstraintType($column_name, $table);
			$key = !is_null($constraint_type['key']) ? $constraint_type['key'] : '';
			
			// Data Type
			$data_type = $this->getDataType($row);
			
			// Length
			$field_length = $this->getFieldLength($row);
			
			// Auto-increment
			if($column_name == 'id' or (preg_match("#^[a-zA-Z0-9]+(_id)$#", $column_name) && $row['ORDINAL_POSITION'] == 1))
				$extra = 'auto_increment';
			else
				$extra = null;
			
			$enum = $this->getCheckConstraint($column_name, $table);
			
			$fields[$column_name] = array(
				"order"=>$row['ORDINAL_POSITION'],
				"default"=>$row['COLUMN_DEFAULT'],
				"null"=>$row['IS_NULLABLE'],
				"type"=>$data_type,
				"max_length"=>$field_length,
				"n_int"=>$row['NUMERIC_PRECISION'],
				"n_precision"=>$row['NUMERIC_PRECISION_RADIX'],	
				"key"=>$key,
				"extra"=>$extra,
				"enum"=>$enum
			);
			
			$primary = $this->getInformationKey($column_name, $table);
			
			if($primary) $structure['primary_key'] = $primary;
			if($key) $structure['keys'][] = $key;
		}
		$structure['fields'] = $fields;

		return $structure;
	}
	
	/**
	 * Ricava il tipo di dato
	 * 
	 * Il tipo di dato deve essere compatibile con quelli definiti in Model::dataType().
	 * 
	 * @param array $info
	 * @return string
	 */
	private function getDataType($info) {
		
		$data_type = $info['DATA_TYPE'];
		
		if(($data_type == 'varchar' || $data_type == 'nvarchar') && $info['CHARACTER_MAXIMUM_LENGTH'] == '-1')
			$data_type = 'text';
		elseif($data_type == 'nchar' || $data_type == 'nvarchar' || $data_type == 'varchar')
			$data_type = 'char';
		
		return $data_type;
	}
	
	/**
	 * Ricava il numero di caratteri di un campo
	 * 
	 * @param array $info
	 * @return integer
	 */
	private function getFieldLength($info) {
		
		$data_type = $info['DATA_TYPE'];
		$maximum_length = $info['CHARACTER_MAXIMUM_LENGTH'];
		$numeric_precision = $info['NUMERIC_PRECISION'];
		$numeric_precision_radix = $info['NUMERIC_PRECISION_RADIX'];
		$numeric_scale = $info['NUMERIC_SCALE'];
		$datetime_precision = $info['DATETIME_PRECISION'];
		
		if(is_int($maximum_length))
		{
			$length = $maximum_length;
		}
		elseif($maximum_length == -1)
		{
			// varchar(max)
		}
		elseif(is_int($numeric_precision))
		{
			$length = $numeric_precision;
			
			if(is_int($numeric_scale) && $numeric_scale > 0)
				$length = $numeric_precision+1;
		}
		elseif($datetime_precision !== null)
		{
			if($data_type == 'date')
			{
				$length = 10;
			}
			elseif($data_type == 'time')
			{
				$length = 8;
			}
			elseif($data_type == 'datetime' || $data_type == 'datetime2')
			{
				$length = 19;
			}
			else $length = 20;
		}
		else $length = null;
		
		return $length;
	}
	
	/**
	 * Verifica se una colonna è una chiave
	 * 
	 * @param string $column
	 * @param string $table
	 * @return array(key, name)
	 * 
	 * Recupera il nome della chiave: \n
	 *   - @a PRIMARY KEY
	 *   - @a UNIQUE
	 *   - @a FOREIGN KEY
	 * e il suo nome (ad es. PK__page_ent__3213E83FAC2C67F3, UQ__page_ent__32DD1E4C35F37AB2)
	 */
	private function getConstraintType($column, $table) {
		
		$query = "
		SELECT C.CONSTRAINT_TYPE, K.CONSTRAINT_NAME
		FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS AS C
		JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS K
		ON C.TABLE_NAME = K.TABLE_NAME
		AND K.TABLE_NAME = '$table' 
		AND K.COLUMN_NAME = '$column' 
		AND C.CONSTRAINT_CATALOG = K.CONSTRAINT_CATALOG
		AND C.CONSTRAINT_SCHEMA = K.CONSTRAINT_SCHEMA
		AND C.CONSTRAINT_NAME = K.CONSTRAINT_NAME";
		$a = $this->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach($a AS $b)
			{
				return array('key'=>$b['CONSTRAINT_TYPE'], 'name'=>$b['CONSTRAINT_NAME']);
			}
		}
		else return array('key'=>null, 'name'=>null);
	}
	
	/**
	 * Verifica se una colonna ha un vincolo CHECK
	 * 
	 * @param string $column
	 * @param string $table
	 * @return string
	 */
	private function getCheckConstraint($column, $table) {
		
		$check_name = 'CK_'.$table.'_'.$column;
		
		$query = "SELECT CHECK_CLAUSE FROM INFORMATION_SCHEMA.CHECK_CONSTRAINTS WHERE CONSTRAINT_NAME='$check_name'";
		$a = $this->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach($a AS $b)
			{
				$check_clause = $b['CHECK_CLAUSE'];
				
				return $check_clause;
			}
		}
		else return null;
	}
	
	/**
	 * Verifica se un campo è una particolare chiave e, in caso positivo, ne recupera il nome
	 * 
	 * @param string $column
	 * @param string $table
	 * @param string $key nome della chiave da ricercare
	 *   - @a PRI, primary key
	 *   - @a UNI, unique key
	 *   - @a FOR, foreign key
	 * @return mixed
	 */
	private function getInformationKey($column, $table, $key=null) {
		
		if($key == 'FOR')
			$key_name = 'FOREIGN KEY';
		elseif($key == 'UNI')
			$key_name = 'UNIQUE';
		else
			$key_name = 'PRIMARY KEY';
		
		$query = "
			SELECT K.CONSTRAINT_NAME
			FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS AS C
			JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS K
			ON C.TABLE_NAME = K.TABLE_NAME
			AND C.CONSTRAINT_CATALOG = K.CONSTRAINT_CATALOG
			AND C.CONSTRAINT_SCHEMA = K.CONSTRAINT_SCHEMA
			AND C.CONSTRAINT_NAME = K.CONSTRAINT_NAME
			WHERE C.CONSTRAINT_TYPE = '$key_name'
			AND K.COLUMN_NAME = '$column'
			AND K.TABLE_NAME = '$table'";
		$a = $this->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach($a AS $b)
			{
				$name = $b['CONSTRAINT_NAME'];
			}
		}
		else $name = null;
		
		return $name;
	}

	/**
	 * @see DbManager::getFieldsName()
	 */
	public function getFieldsName($table) {

		$fields = array();
		
		$query = "SELECT COLUMN_NAME AS Field
		FROM INFORMATION_SCHEMA.COLUMNS
		WHERE TABLE_SCHEMA = '".DB_SCHEMA."'
		AND TABLE_NAME = '$table'";
		
		$res = mssql_query($query);
		while($row = mssql_fetch_assoc($res)) {
			$results[] = $row;
		}
		$this->freeresult($res);

		foreach($results as $r) {
			$fields[] = $r['Field'];
		}

		return $fields;
	}

	/**
	 * @see DbManager::getNumRecords()
	 */
	public function getNumRecords($table, $where=null, $field='id') {

		$tot = 0;

		$qwhere = $where ? "WHERE ".$where : "";
		$query = "SELECT COUNT($field) AS tot FROM $table $qwhere";
		$res = $this->selectquery($query);
		if($res) {
			$tot = $res[0]['tot'];
		}

		return (int) $tot;
	}
	
	/**
	 * @see DbManager::query()
	 * @see limitQuery()
	 */
	public function query($fields, $tables, $where=null, $options=array()) {

		$order = gOpt('order', $options, null);
		$limit = gOpt('limit', $options, null);
		$debug = gOpt('debug', $options, false);
		$distinct = gOpt('distinct', $options, null);
		
		$qfields = is_array($fields) ? implode(",", $fields) : $fields;
		$qtables = is_array($tables) ? implode(",", $tables) : $tables;
		$qwhere = $where ? "WHERE ".$where : "";
		$qorder = $order ? "ORDER BY $order" : "";
		
		if($distinct) $qfields = $distinct.", ".$qfields;
		
		if(is_array($limit) && count($limit))	// Paginazione
		{
			return $this->limitQuery($fields, $qtables, $where, $options);
		}
		
		if(is_string($limit))
			$top = $limit;
		else $top = '';
		
		$query = "SELECT $top $qfields FROM $qtables $qwhere $qorder";
		
		if($debug) echo $query;
		
		return $query;
	}
	
	private function limitQuery($fields, $tables, $where=null, $options=array()) {
		
		$order = gOpt('order', $options, null);
		$limit = gOpt('limit', $options, null);
		$debug = gOpt('debug', $options, false);
		$distinct = gOpt('distinct', $options, null);
		
		$qtables = is_array($tables) ? implode(",", $tables) : $tables;
		$qwhere = $where ? "WHERE ".$where : "";
		
		$offset = $limit[0];
		$range = $limit[1];
		settype($offset, 'int');
		
		if(is_string($fields)) $fields = explode(',', $fields);
		
		$clean_fields = array();	// solo i nomi dei campi
		$func_fields = array();		// nomi dei campi comprensivi delle eventuali funzioni (ad es. distinct)
		
		foreach($fields AS $f)
		{
			$field = trim($f);
			preg_match("#^([a-zA-Z ]+)\(([a-zA-Z0-9_]+)\)$#", $field, $matches);
			if(isset($matches[2]) && $matches[2])
			{
				$clean_fields[] = $matches[2];
			}
			else
			{
				$clean_fields[] = $field;
			}
			
			$func_fields[] = $field;
		}
		
		if($order)
		{
			$order_field = array();
			$split_order_field = explode(',', $order);	// per gestire i casi tipo: name ASC, descr DESC
			
			foreach($split_order_field AS $s)
			{
				$a_order = array();
				$split_field = explode(' ', $s);
				foreach($split_field AS $f)
				{
					if(preg_match("#\.#", $f))	// ricerco la ricorrenza [nome_tabella].[nome_campo]
					{
						$a_field = explode('.', $f);
						$field_name = trim($a_field[1]);
						$a_order[] = $field_name;
						
						// verifico se il campo di ordinamento è presente nell'elenco dei campi del select
						// in caso negativo lo aggiungo all'elenco dei nomi comprensivi delle eventuali funzioni (subquery)
						if(!in_array($field_name, $clean_fields))
							$func_fields[] = $field_name;
					}
					else $a_order[] = trim($f);
				}
				$order_field[] = implode(' ', $a_order);
			}
			
			$clean_order = "ORDER BY ".implode(', ', $order_field);
			$qorder = "ORDER BY ".$order;
		}
		else
		{
			$clean_order = $qorder = "ORDER BY id";
		}
		
		$clean_fields = implode(', ', $clean_fields);
		$func_fields = implode(', ', $func_fields);
		
		$query = "SELECT $clean_fields FROM ( 
			SELECT $func_fields, row_number () over ($qorder) - 1 as rn
			FROM $qtables $qwhere) rn_subquery 
		WHERE rn between $offset and ($offset+$range)-1 $clean_order";
		
		if($debug) echo $query;
		
		return $query;
	}

	/**
	 * @see DbManager::select()
	 */
	public function select($fields, $tables, $where=null, $options=array()) {

		$query = $this->query($fields, $tables, $where, $options);
		
		return $this->selectquery($query);
	}
	
	/**
	 * @see DbManager::insert()
	 */
	public function insert($fields, $table, $debug=false) {

		if(is_array($fields) && count($fields) && $table)
		{
			$a_fields = array();
			$a_values = array();
			
			foreach($fields AS $field=>$value)
			{
				$a_fields[] = $field;
				
				if(is_array($value))
				{
					if(array_key_exists('sql', $value))
					{
						$mb_value = convertToDatabase($value['sql'], 'CP1252');
						$a_fields[] = "[$field]=".$mb_value;
					}
				}
				else
				{
					$mb_value = convertToDatabase($value, 'CP1252');
					$a_values[] = ($value !== null) ? "'$mb_value'" : null;	// VERIFICARE
				}
			}
			
			$s_fields = "[".implode('],[', $a_fields)."]";
			$s_values = implode(",", $a_values);
			
			$query = "INSERT INTO $table ($s_fields) VALUES ($s_values)";
			
			if($debug) echo $query;
			
			return $this->actionquery($query);
		}
		else return false;
	}
	
	/**
	 * @see DbManager::update()
	 */
	public function update($fields, $table, $where, $debug=false) {

		if(is_array($fields) && count($fields) && $table)
		{
			$a_fields = array();
			
			foreach($fields AS $field=>$value)
			{
				if(is_array($value))
				{
					if(array_key_exists('sql', $value))
					{
						$mb_value = convertToDatabase($value['sql'], 'CP1252');
						$a_fields[] = "[$field]=".$mb_value;
					}
				}
				else
				{
					$mb_value = convertToDatabase($value, 'CP1252');
					$a_fields[] = "[$field]='$mb_value'";	//$a_fields[] = ($value == 'null') ? "[$field]=$value" : "[$field]='$mb_value'";
				}
			}
			
			$s_fields = implode(",", $a_fields);
			$s_where = $where ? " WHERE ".$where : "";
			
			$query = "UPDATE $table SET $s_fields".$s_where;
			
			if($debug) echo $query;
			
			return $this->actionquery($query);
		}
		else return false;
	}
	
	/**
	 * @see DbManager::delete()
	 */
	public function delete($table, $where, $debug=false) {

		if(!$table) return false;
		
		$s_where = $where ? " WHERE ".$where : '';
		
		$query = "DELETE FROM $table".$s_where;
		
		if($debug) echo $query;
		
		return $this->actionquery($query);
	}

	/**
	 * @see DbManager::drop()
	 */
	public function drop($table) {

		if(!$table) return false;
		
		$query = "DROP $table";
		
		return $this->actionquery($query);
	}

  /**
	 * @see DbManager::columnHasValue()
	 */
	public function columnHasValue($table, $field, $value, $options=array()) {
		
		$except_id = gOpt('except_id', $options, null);
		
		$where = $field."='$value'";
		if($except_id) $where .= " AND id!='$except_id'";
		
		$rows = $this->select($field, $table, $where);
		return $rows and count($rows) ? true : false;
	}
	
	/**
	 * @see DbManager::join()
	 */
	public function join($table, $condition, $option) {
		
		$join = $table;
		if($condition) $join .= ' ON '.$condition;
		if($option) $join = strtoupper($option).' '.$join;
		
		return $join;
	}
	
	/**
	 * @see DbManager::union()
	 * 
	 * In mssql possono essere utilizzati gli operatori: \n
	 * - UNION, elimina le righe duplicate dai risultati combinati delle istruzioni SELECT \n
	 * - UNION ALL, mostra i record duplicati
	 */
	public function union($queries, $options=array()) {
		
		$debug = gOpt('debug', $options, false);
		$instruction = gOpt('instruction', $options, 'UNION');
		
		if(count($queries))
		{
			$query = implode(" $instruction ", $queries);
			
			if($debug) echo $query;
			
			return $this->selectquery($query);
		}
		return array();
	}
	
	/**
	 * @see DbManager::restore()
	 */
	public function restore($table, $filename, $options=array()) {      
	
		$fields = gOpt('fields', $options, null);
		$delim = gOpt('delim', $options, ',');
		$enclosed = gOpt('enclosed', $options, '"');
		$escaped = gOpt('escaped', $options, '\\');
		$lineend = gOpt('lineend', $options, '\\r\\n');
		$hasheader = gOpt('hasheader', $options, false);
		
		$ignore = $hasheader ? "IGNORE 1 LINES " : "";
		if($fields) $fields = "(".implode(',', $fields).")";
		
		$query = 
		"LOAD DATA INFILE '".$filename."' INTO TABLE ".$table." ".
		"FIELDS TERMINATED BY '".$delim."' ENCLOSED BY '".$enclosed."' ".
		"ESCAPED BY '".$escaped."' ".
		"LINES TERMINATED BY '".$lineend."' ".$ignore.$fields;
		return $this->actionquery($query);
	}
	
	/**
	 * @see DbManager::dump()
	 * 
	 * Per poter effettuare questa operazione occorre: \n
	 *   - assegnare il permesso FILE all'utente del database: GRANT FILE ON *.* TO 'dbuser'@'localhost';
	 *   - la directory di salvataggio deve avere i permessi 777, oppure deve avere come proprietario l'utente di sistema mysql (gruppo mysql)
	 */
	public function dump($table, $filename, $options=array()) {
		
		$delim = gOpt('delim', $options, ',');
		$enclosed = gOpt('enclosed', $options, '"');
		
		$query = "SELECT * INTO OUTFILE '".$filename."' 
		FIELDS TERMINATED BY '".$delim."' ENCLOSED BY '".$enclosed."' 
		FROM $table";
		if($this->actionquery($query))
			return $filename;
		else
			return null;
	}
	
	/**
	 * @see DbManager::escapeString()
	 */
	public function escapeString($string) {
		
		/*if(!isset($data) or empty($data)) return '';
		if(is_numeric($data)) return $data;
		
		$non_displayables = array(
			'/%0[0-8bcef]/',            // url encoded 00-08, 11, 12, 14, 15
			'/%1[0-9a-f]/',             // url encoded 16-31
			'/[\x00-\x08]/',            // 00-08
			'/\x0b/',                   // 11
			'/\x0c/',                   // 12
			'/[\x0e-\x1f]/'             // 14-31
			);
		foreach ($non_displayables as $regex)
			$data = preg_replace($regex, '', $data);
		$data = str_replace("'", "''", $data);
		return $data;
		*/
		
		/*if(is_numeric($string))
			return $string;
		$unpacked = unpack('H*hex', $string);
		return '0x' . $unpacked['hex'];
		*/
		
		$string = str_replace("'", "''", $string);
		$string = str_replace("\0", "[NULL]", $string);
		return $string;
	}
}

?>