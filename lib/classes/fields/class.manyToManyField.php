<?php
/**
 * @file class.manyToManyField.php
 * @brief Contiene la classe manyToManyField
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Campo di tipo many to many (estensione)
 * 
 * I valori da associare al campo risiedono in una tabella esterna e i parametri per accedervi devono essere definiti nelle opzioni del campo. \n
 * Tipologie di input associabili: multicheck
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class manyToManyField extends field {

	/**
	 * Proprietà dei campi specifiche del tipo di campo
	 */
	protected $_fkey_table, $_fkey_id, $_fkey_field, $_fkey_where, $_fkey_order;
	protected $_enum;
	
	/**
	 * Costruttore
	 * 
	 * @param array $options array associativo di opzioni del campo del database
	 *   - opzioni generali definite come proprietà nella classe field()
	 *   - @b fkey_table (string): nome della tabella dei dati
	 *   - @b fkey_id (string): nome del campo della chiave nel SELECT (default: id)
	 *   - @b fkey_field (mixed): nome del campo o dei campi dei valori nel SELECT
	 *     - @a string, nome del campo
	 *     - @a array, nomi dei campi da concatenare, es. array('firstname', 'lastname')
	 *   - @b fkey_where (mixed): condizioni della query
	 *     - @a string, es. "cond1='$cond1' AND cond2='$cond2'"
	 *     - @a array, es. array("cond1='$cond1'", "cond2='$cond2'")
	 *   - @b fkey_order (string): ordinamento dei valori (es. name ASC)
	 * @return void
	 */
	function __construct($options) {

		parent::__construct($options);
		
		$this->_default_widget = 'multicheck';
		$this->_value_type = 'array';
		
		$this->_fkey_table = array_key_exists('fkey_table', $options) ? $options['fkey_table'] : null;
		$this->_fkey_id = array_key_exists('fkey_id', $options) ? $options['fkey_id'] : 'id';
		$this->_fkey_field = array_key_exists('fkey_field', $options) ? $options['fkey_field'] : null;
		$this->_fkey_where = array_key_exists('fkey_where', $options) ? $options['fkey_where'] : '';
		$this->_fkey_order = array_key_exists('fkey_order', $options) ? $options['fkey_order'] : '';
	}
	
	public function __toString() {

		$db = db::instance();
		
		$field = $this->defineField($db);
		if(!$field) return null;

		$parts = array();
		foreach($this->_value as $v) {
			$parts[] = $db->getFieldFromId($this->_fkey_table, $field, $this->_fkey_id, $v);
		} 

		return (string) implode(', ', $parts);
	}
	
	public function getForeignKeyTable() {
		
		return $this->_fkey_table;
	}
	
	public function setForeignKeyTable($value) {
		
		$this->_fkey_table = $value;
	}
	
	public function getForeignKeyId() {
		
		return $this->_fkey_id;
	}
	
	public function setForeignKeyId($value) {
		
		$this->_fkey_id = $value;
	}
	
	public function getForeignKeyField() {
		
		return $this->_fkey_field;
	}
	
	public function setForeignKeyField($value) {
		
		$this->_fkey_field = $value;
	}
	
	public function getForeignKeyWhere() {
		
		return $this->_fkey_where;
	}
	
	public function setForeignKeyWhere($value) {
		
		$this->_fkey_where = $value;
	}
	
	public function getForeignKeyOrder() {
		
		return $this->_fkey_order;
	}
	
	public function setForeignKeyOrder($value) {
		
		$this->_fkey_order = $value;
	}
	
	public function getEnum() {
		
		return $this->_enum;
	}
	
	public function setEnum($value) {
		
		$this->_enum = $value;
	}
	
	/**
	 * Formatta il nome del campo da ricercare, tenendo conto di eventuali concatenamenti
	 * 
	 * @param object $db
	 * @return string
	 */
	private function defineField($db=null) {
		
		if(is_array($this->_fkey_field) && count($this->_fkey_field))
		{
			if(sizeof($this->_fkey_field) > 1)
			{
				$array = array();
				foreach($this->_fkey_field AS $value)
				{
					$array[] = $value;
					$array[] = '\' \'';
				}
				array_pop($array);
				
				if(!$db) $db = db::instance();
				
				$fields = $db->concat($array);
			}
			else $fields = $this->_fkey_field[0];
		}
		elseif(is_string($this->_fkey_field) && $this->_fkey_field)
		{
			$fields = $this->_fkey_field;
		}
		else $fields = null;
		
		return $fields;
	}
	
	/**
	 * Imposta la query di selezione dei dati di una chiave esterna
	 * 
	 * @see defineField()
	 * @return string
	 */
	private function foreignKey() {
		
		if(is_null($this->_fkey_table) || is_null($this->_fkey_field))
			return null;
		
		$field = $this->defineField();
		if(!$field) return null;
		
		if(is_array($this->_fkey_where) && count($this->_fkey_where))
		{
			$where = implode(" AND ", $this->_fkey_where);
		}
		elseif(is_string($this->_fkey_where) && $this->_fkey_where)
		{
			$where = $this->_fkey_where;
		}
		else $where = '';
		
		if($where) $where = "WHERE $where";
		if($this->_fkey_order) $order = "ORDER BY ".$this->_fkey_order;
		
		$query = "SELECT {$this->_fkey_id}, $field FROM {$this->_fkey_table} $where $order";
		
		return $query;
	}
	
	/**
	 * Stampa l'elemento del form
	 * 
	 * @param object $form
	 * @param array $options opzioni dell'elemento del form
	 * @return string
	 */
	public function formElement($form, $options) {
		
		$this->_enum = $this->foreignKey();
		$this->_name .= "[]";

		$options['table'] = $this->_fkey_table;
		$options['field'] = $this->_fkey_field;
		$options['idName'] = $this->_fkey_id;
		
		return parent::formElement($form, $options);
	}

	/**
	 * Formatta un elemento input per l'inserimento in database
	 * 
	 * @see cleanVar()
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b value_type (string): tipo di valore
	 *   - @b method (array): metodo di recupero degli elementi del form
	 *   - @b escape (boolean): evita che venga eseguito il mysql_real_escape_string sul valore del campo
	 *   - @b asforminput (boolean)
	 * @return mixed
	 */
	public function clean($options=null) {
		
		$value_type = $this->_value_type;
		$method = isset($options['method']) ? $options['method'] : $_POST;
		$escape = gOpt('escape', $options, true);
		
		$value = cleanVar($method, $this->_name, $value_type, null, array('escape'=>$escape));

		if(gOpt('asforminput', $options, false)) {
			return $value;
		}

		if($value) $value = implode(',', $value);
		return $value;
	}

	/**
	 * Definisce la condizione WHERE per il campo
	 * 
	 * @param string $value
	 * @return string
	 */
	public function filterWhereClause($value) {

		$parts = array();
		foreach($value as $v) {
			$parts[] = $this->_table.".".$this->_name." REGEXP '[[:<:]]".$v."[[:>:]]'";
		}

		return "(".implode(' OR ', $parts).")";
	}
}
?>
