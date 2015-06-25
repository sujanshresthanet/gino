<?php
/**
 * @file class.DatetimeFieldBuild.php
 * @brief Contiene la definizione ed implementazione della classe Gino.DatetimeFieldBuild
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

loader::import('class/fields', '\Gino\FieldBuild');

/**
 * @brief Gestisce i campi di tipo DATETIME
 *
 * @description Impostando opportunamente le proprietà @a $_auto_now_add e @a $_auto_now è possibile gestire il campo datetime 
 * in modo che venga impostato soltanto quando viene creato l'oggetto oppure ogni volta che l'oggetto viene salvato.
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class DatetimeFieldBuild extends FieldBuild {

    /**
     * Proprietà dei campi specifiche del tipo di campo
     */
    protected $_auto_now, $_auto_now_add;

    /**
     * Costruttore
     *
     * @see Gino.FieldBuild::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe FieldBuild()
     *   - @b auto_now (boolean): imposta automaticamente il valore del campo al tempo/ora corrente ogni volta che l'oggetto viene salvato (default: true)
     *   - @b auto_now_add (boolean): imposta automaticamente il valore del campo al tempo/ora corrente la prima volta che l'oggetto viene creato (default: true)
     */
    function __construct($options) {

        parent::__construct($options);

        $this->_auto_now = array_key_exists('auto_now', $options) ? $options['auto_now'] : true;
        $this->_auto_now_add = array_key_exists('auto_now_add', $options) ? $options['auto_now_add'] : true;

        if($this->_auto_now || $this->_auto_now_add) {
            $this->_widget = null;
            $this->_required = false;
        }
    }
    
    /**
     * @brief Rappresentazione a stringa dell'oggetto
     * @return valore del campo
     */
    function __toString() {
    
    	return (string) $this->_value;
    }

    /**
     * @brief Getter della proprietà auto_now (update ad ogni modifica del record)
     * @return proprietà auto_now
     */
    public function getAutoNow() {

        return $this->_auto_now;
    }

    /**
     * @brief Setter della proprietà auto_now
     * @param bool $value
     * @return void
     */
    public function setAutoNow($value) {

        if(is_bool($value)) $this->_auto_now = $value;
    }

    /**
     * @brief Getter della proprietà auto_now_add (update in inserimento record)
     * @return proprietà auto_now_add
     */
    public function getAutoNowAdd() {

        return $this->_auto_now_add;
    }

    /**
     * @brief Setter della proprietà auto_now_add
     * @param bool $value
     * @return void
     */
    public function setAutoNowAdd($value) {

        if(is_bool($value)) $this->_auto_now_add = $value;
    }

    /**
     * @brief Definisce la condizione WHERE per il campo
     * @see Gino.Field::filterWhereClause()
     * 
     * @param string $value
     * @param array $options
     *   array associativo di opzioni
     *   - @b operator (string): operatore di confronto della data
     * @return where clause
     * 
     * VEDERE ADMINLIST
     */
    public function filterWhereClause($value, $options=array()) {

        $operator = gOpt('operator', $options, null);
        if(is_null($operator)) $operator = '=';

        return $this->_table.".".$this->_name." $operator '".$value."'";
    }

    /**
     * @brief Ripulisce un input per l'inserimento in database
     * @see Gino.Field::clean()
     */
    public function clean($options=null) {

        if($this->_auto_now || $this->_auto_now_add)
        {
            if(!$this->_value || ($this->_value && $this->_auto_now))
            {
                $date = date("Y-m-d H:i:s");
            }
            else
            {
                $date = $this->_value;
            }
            return $date;
        }
        else return parent::clean($options);
    }
}
