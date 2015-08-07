<?php
/**
 * @file class.PasswordWidget.php
 * @brief Contiene la definizione ed implementazione della classe Gino.PasswordWidget
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Campi di tipo password nei form
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class PasswordWidget extends Widget {

	/**
	 * @see Gino.Widget::printInputForm()
	 */
	public function printInputForm($form, $options) {
	
		parent::printInputForm($form, $options);
		
		$value = $this->_form->retvar($this->_name, htmlInput($this->_value));
		
		$buffer = $this->_form->cinput($this->_name, 'password', $value, $this->_label, $options);
		
		return $buffer;
	}
}
