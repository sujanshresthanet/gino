<?php
/**
 * @file class.textField.php
 * @brief Contiene la classe textField
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Campo di tipo TEXT
 * 
 * Tipologie di input associabili: textarea, testo, editor html
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class textField extends field {

	/**
	 * Proprietà dei campi specifiche del tipo di campo
	 */
	protected $_trnsl;
	
	/**
	 * Costruttore
	 * 
	 * @param array $options array associativo di opzioni del campo del database
	 *   - opzioni generali definite come proprietà nella classe field()
	 *   - @b trnsl (boolean): campo con traduzioni
	 * @return void
	 */
	function __construct($options) {
		
		parent::__construct($options);
		
		$this->_default_widget = 'textarea';
		
		$this->_trnsl = isset($options['trnsl']) ? $options['trnsl'] : true;
	}
	
	public function getTrnsl() {
		
		return $this->_trnsl;
	}
	
	public function setTrnsl($value) {
		
		if(is_bool($value)) $this->_trnsl = $value;
	}
	
	/**
	 * Stampa l'elemento del form
	 * 
	 * @param object $form
	 * @param array $options opzioni dell'elemento del form
	 * @return string
	 */
	public function formElement($form, $options) {
		
		if(!isset($options['trnsl'])) $options['trnsl'] = $this->_trnsl;
		if(!isset($options['field'])) $options['field'] = $this->_name;
		
		return parent::formElement($form, $options);
	}
}
?>
