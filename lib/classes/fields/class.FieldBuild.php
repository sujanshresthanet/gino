<?php
/**
 * @file class.FieldBuild.php
 * @brief Contiene la definizione ed implementazione della classe Gino.FieldBuild
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

use \Gino\Http\Request;

/**
 * @brief Gestisce i campi delle tabelle
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class FieldBuild {

    /**
     * @brief Proprietà dei campi
     */
    protected $_name,
    	$_label,
    	$_default,
    	$_lenght,
    	$_auto_increment,
    	$_primary_key,
    	$_unique_key,
    	$_table,
    	$_required,
    	$_widget,
    	$_value_type,
    	$_int_digits,
    	$_decimal_digits;

    /**
     * @brief Istanza del modello cui il campo appartiene
     * @var Gino.Model
     */
    protected $_model;
    
    /**
     * @brief Valore del campo
     * @var mixed
     */
    protected $_value;
    
    /**
     * @brief Elenco delle opzioni del modello e del tipo di campo
     * @var array
     */
    protected $_options;

    /**
     * Costruttore
     * 
     * @param array $options array associativo di opzioni del campo di una tabella
     *   // opzioni delle colonne (caratteristiche del tipo di campo)
     *   // opzioni del modello
     *   - @b model (object):
     *   - @b value (mixed):
     */
    function __construct($options) {

    	$this->_options = $options;
    	
        $this->_name = $options['name'];
    	$this->_label = $options['label'];
    	$this->_default = $options['default'];
    	$this->_lenght = $options['lenght'];
    	$this->_auto_increment = $options['auto_increment'];
    	$this->_primary_key = $options['primary_key'];
    	$this->_unique_key = $options['unique_key'];
    	$this->_table = $options['table'];
    	$this->_required = $options['required'];
    	$this->_widget = $options['widget'];
    	$this->_value_type = $options['value_type'];
    	$this->_int_digits = $options['int_digits'];
    	$this->_decimal_digits = $options['decimal_digits'];
    	
    	$this->_model = $options['model'];
    	
    	if(array_key_exists('value', $options)) {
    		$this->_value = $options['value'];
    	}
    	else {
    		$value = $this->_model->{$this->_name};
    		$this->_value = $value;
    		$this->_options['value'] = $value;
    	}
    }

    /**
     * @brief Rappresentazione a stringa dell'oggetto
     * @return valore del campo
     */
    public function __toString() {

        return (string) $this->_value;
    }
    
    /**
     * @brief Indica se il campo può essere utilizzato come ordinamento nella lista della sezione amministrativa
     * @return TRUE se puo' essere utilizzato per l'ordinamento, FALSE altrimenti
     */
    public function canBeOrdered() {
    
    	return TRUE;
    }

    /**
     * @brief Getter della proprietà name
     * @return nome del campo
     */
    public function getName() {

        return $this->_name;
    }

    /**
     * @brief Setter della proprietà name
     * @param string $name
     * @return void
     */
    public function setName($name) {

        $this->_name = (string) $name;
    }

    /**
     * @brief Getter della proprietà value
     * @return valore del campo
     */
    public function getValue() {

        return $this->_value;
    }

    /**
     * @brief Setter della proprietà value
     * @param mixed $value
     * @return void
     */
    public function setValue($value) {

        $this->_value = $value;
    }
    
    /**
     * @brief Stampa un elemento del form facendo riferimento al valore della chiave @a widget
     *
     * Nella chiamata del form occorre definire la chiave @a widget nell'array degli elementi input. \n
     * Nel caso in cui la chiave @a widget non sia definita, verrà presa la chiave di default specificata nelle proprietà del modello. \n
     * Esempio
     * @code
     * array(
     *   'ctg'=>array('required'=>true),
     *   'field_text1'=>array(
     *     'widget'=>'editor',
     *     'notes'=>false,
     *     'img_preview'=>false,
     *     'fck_height'=>100),
     *   'field_text2'=>array('maxlength'=>$maxlength_summary, 'id'=>'summary', 'rows'=>6, 'cols'=>55)
     * )
     * @endcode
     *
     * @see Gino.Widget::printInputForm()
     * @param \Gino\Form $form istanza di Gino.Form
     * @param array $options opzioni dell'elemento del form
     *   - opzioni dei metodi della classe Form
     *   - opzioni che sovrascrivono le impostazioni del campo/modello
     *     - @b widget (string): tipo di input form; può assumenre uno dei seguenti valori
     *       - @a hidden
     *       - @a constant
     *       - @a select
     *       - @a radio
     *       - @a checkbox
     *       - @a multicheck
     *       - @a editor
     *       - @a textarea
     *       - @a float
     *       - @a date
     *       - @a datetime
     *       - @a time
     *       - @a password
     *       - @a file
     *       - @a image
     *       - @a email
     *     - @b required (boolean): campo obbligatorio
     * @return controllo del campo, html
     */
    public function formElement(\Gino\Form $form, $options) {
    
    	$widget = isset($options['widget']) ? $options['widget'] : $this->_widget;
    	
    	if($widget == null) {
    		return '';
    	}
    	else {
    		
    		if(!array_key_exists('required', $options)) {
    			$options['required'] = $this->_required;
    		}
    		$opt = array_merge($this->_options, $options);
    		
    		$wìdget_class = "\Gino\\".ucfirst($widget)."Widget";
    		
    		$obj = new $wìdget_class();
    		return $obj->printInputForm($form, $opt);
    	}
    }
    
    /**
     * @brief Stampa un elemento del form di filtri area amministrativa
     * @param \Gino\Form $form istanza di Gino.Form
     * @param array $options
     *   - @b default (mixed): valore di default
     * @return controllo del campo, html
     */
    public function formFilter(\Gino\Form $form, $options)
    {
    	$options['required'] = FALSE;
    	$options['is_filter'] = TRUE;
    	
    	return $this->formElement($form, $options);
    }
    
    /**
     * @brief Ripulisce un input usato come filtro in area amministrativa
     * @param $options
     *   array associativo di opzioni
     *   - @b escape (boolean): evita che venga eseguito il mysql_real_escape_string sul valore del campo
     * @return input ripulito
     */
    public function cleanFilter($options)
    {
    	$options['asforminput'] = TRUE;
    	return $this->clean($options);
    }
    
    /**
     * @brief Ripulisce un input per l'inserimento in database
     *
     * @see Gino.cleanVar()
     * @param array $options
     *   array associativo di opzioni
     *   - @b value_type (string): tipo di valore
     *   - @b method (array): metodo di recupero degli elementi del form
     *   - @b escape (boolean): evita che venga eseguito il mysql_real_escape_string sul valore del campo
     * @return input ripulito
     */
    public function clean($options=null) {
    
    	$request = Request::instance();
    	$value_type = isset($options['value_type']) ? $options['value_type'] : $this->_value_type;
    	$method = isset($options['method']) ? $options['method'] : $request->POST;
    	$escape = gOpt('escape', $options, TRUE);
    
    	return cleanVar($method, $this->_name, $value_type, null, array('escape'=>$escape));
    }
    
    /**
     * @brief Valore del campo di tabella per la visualizzazione
     *
     * @param mixed $value
     * @return mixed
     */
    public function retrieveValue() {
    
    	return $this->_value;
    }
}
