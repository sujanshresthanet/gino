<?php
/*================================================================================
    Gino - a generic CMS framework
    Copyright (C) 2005  Otto Srl - written by Marco Guidotti

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

   For additional information: <opensource@otto.to.it>
================================================================================*/

class options extends pub {

	private $_class, $_class_prefix;
	private $_tbl_options;
	private $_instance;
	private $_title;

	private $_action;
	
	function __construct($class, $instance){
		
		parent::__construct();

		$this->_title = _("Opzioni");

		$this->setData($instance, $class);
		
		$this->_action = cleanVar($_REQUEST, 'action', 'string', '');
	}
	
	private function setData($instance, $class) {
		
		$this->_instance = $instance;
		$this->_instanceName = $this->_db->getFieldFromId($this->_tbl_module, 'name', 'id', $instance);

		if($this->_instance && empty($this->_instanceName)) exit(error::syserrorMessage("options", "setData", "Istanza di ".$class." non trovata", __LINE__));

		if($class) $this->_class = $class;
		else exit(error::syserrorMessage("options", "setData", "Classe ".$class." inesistente", __LINE__));

		if(!$this->_instance) $this->_instanceName = $this->_class; 		
		
		$this->_class_prefix = $this->field_class('tbl_name', $this->_class);
		$this->_tbl_options = $this->_class_prefix.'_opt';

		$this->_return_link = method_exists($class, "manageDoc")? $this->_instanceName."-manageDoc": $this->_instanceName."-manage".ucfirst($class);

	}

	private function field_class($field, $class_name){
		
		$query = "SELECT $field FROM ".$this->_tbl_module_app." WHERE name='$class_name' AND type='class'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach ($a AS $b)
			{
				$value = $b[$field];
			}
		}
		else
		{
			$query = "SELECT $field FROM ".$this->_tbl_module_app." WHERE name='$class_name' AND type='class'";
			$a = $this->_db->selectquery($query);
			if(sizeof($a) > 0)
			{
				foreach ($a AS $b)
				{
					$value = $b[$field];
				}
			}
		}
		return $value;
	}

	private function editableField($field) {
		return ($field != 'id' && $field != 'instance');
	}
	
	public function manageDoc(){

		if($this->_action == $this->_act_insert || $this->_action == $this->_act_modify) return $this->actionOptions();

		$gform = new Form('gform', 'post', true);
		$gform->load('dataform');

		$class_instance = ($this->_instance)?new $this->_class($this->_instance):new $this->_class();
		$table_info = $this->_db->fieldInformations($this->_tbl_options);
		$required = '';

		$query = "SELECT * FROM ".$this->_tbl_options." WHERE instance='".$this->_instance."'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a)>0) {
			foreach($a as $b) {
				$id = $b['id'];
				foreach($table_info AS $f) {
					if($this->editableField($f->name)) {
						// Required
						$field_option = $class_instance->_optionsLabels[$f->name];
						if(is_array($field_option) AND key_exists('label', $field_option))
						{
							if(key_exists('required', $field_option) AND $field_option['required'] == true)
								$required .= $f->name.",";
						}
						else $required .= $f->name.",";
						
						${$f->name} = htmlInput($b[$f->name]);
					}
				}
			}
			$action = $this->_act_modify;
			$submit = _("modifica");
		}
		else {
			$id = '';
			foreach($table_info AS $f) {
				if($this->editableField($f->name)) {
					
					${$f->name} = '';
					
					// Required
					$field_option = $class_instance->_optionsLabels[$f->name];
					if(is_array($field_option) AND key_exists('label', $field_option))
					{
						if(key_exists('required', $field_option) AND $field_option['required'] == true)
							$required .= $f->name.",";
						
						if(key_exists('value', $field_option) AND $field_option['value'] != '')
							${$f->name} = $field_option['value'];
					}
					else $required .= $f->name.",";
				}
			}
			$action = $this->_act_insert;
			$submit = _("inserisci");
		}
	
		$label = $this->field_class('label', $this->_class);
		$htmlsection = new htmlSection(array('type'=>'admin', 'headerTag'=>'header'));

		if(method_exists($this->_class, 'manageDoc')) $function = 'manageDoc';
		else $function = 'manage'.ucfirst($this->_class);

		if($required) $required = substr($required, 0, strlen($required)-1);
		$GINO = $gform->form($this->_home."?evt[".$this->_instanceName."-$function]&block=options", '', $required);
		$GINO .= $gform->hidden('func', 'actionOptions');
		$GINO .= $gform->hidden('action', $action);

		foreach($table_info AS $f) {
		
			if($this->editableField($f->name)) {

				$field_option = $class_instance->_optionsLabels[$f->name];
				
				if(is_array($field_option) AND key_exists('label', $field_option))
				{
					$field_label = $field_option['label'];
					$field_required = key_exists('required', $field_option) ? $field_option['required'] : false;
				}
				else
				{
					$field_label = $field_option;
					$field_required = true;
				}
				
				if($f->type == 'string') {
					$GINO .= $gform->cinput($f->name, 'text', ${$f->name}, $field_label, array("required"=>$field_required, "size"=>40, "maxlength"=>$f->length, "trnsl"=>true, "trnsl_table"=>$this->_tbl_options, "field"=>$f->name, "trnsl_id"=>$id));
				}
				elseif($f->type == 'blob') {
					$GINO .= $gform->ctextarea($f->name, ${$f->name},  $field_label, array("cols"=>'96%', "rows"=>4, "required"=>$field_required, "trnsl"=>true, "trnsl_table"=>$this->_tbl_options, "field"=>$f->name, "trnsl_id"=>$id));
				}
				elseif($f->type == 'int' && $f->length>1) {
					$GINO .= $gform->cinput($f->name, 'text', ${$f->name},  $field_label, array("required"=>$field_required, "size"=>$f->length, "maxlength"=>$f->length));
				}
				elseif($f->type == 'int' && $f->length == 1) {
					$GINO .= $gform->cradio($f->name, ${$f->name}, array(1=>_("si"),0=>_("no")), 'no',  $field_label, array("required"=>$field_required));
				}
				elseif($f->type == 'date') {
					$GINO .= $gform->cinput_date($f->name, dbDateToDate(${$f->name}, '/'),  $field_label, array("required"=>$field_required));
				}
				else $GINO .= "<p>"._("ATTENZIONE! Tipo di campo non supportato")."</p>";

			}
		}
		$GINO .= $gform->cinput('submit_action', 'submit', $submit, '', array("classField"=>"submit"));
		$GINO .= $gform->cform();

		$htmlsection->content = $GINO;

		return $htmlsection->render();
	}

	public function actionOptions() {
	
		$gform = new Form('gform', 'post', false);
		$gform->save('dataform');
		$req_error = $gform->arequired();

		$action = cleanVar($_POST, 'action', 'string', '');

		$table_info = $this->_db->fieldInformations($this->_tbl_options);

		$par_query = $par1_query = $par2_query = '';
		foreach($table_info AS $f) {
			if($this->editableField($f->name)) {
				if($f->type == 'int') {
					${$f->name} = cleanVar($_POST, $f->name, 'int', '');
				}
				elseif($f->type == 'date') {
					${$f->name} = dateToDbDate(cleanVar($_POST, $f->name, 'string', ''), '/');
				}
				else ${$f->name} = cleanVar($_POST, $f->name, 'string', '');
				if($action == $this->_act_insert) {
					$par1_query .= ",".$f->name;
					$par2_query .= ($f->type=='int')? ",".${$f->name} : ",'".${$f->name}."'";
				}	
				elseif($action == $this->_act_modify) {
					$par_query .= ($par_query)?",".$f->name."=":$f->name."=";
					$par_query .= ($f->type=='int')? ${$f->name}:"'".${$f->name}."'";
				}
			}	
		}
		
		if($req_error > 0) 
			exit(error::errorMessage(array('error'=>1), $this->_home."?evt[$this->_return_link]&$link"));

		if($action == $this->_act_insert) $query = "INSERT INTO ".$this->_tbl_options." (instance$par1_query) VALUES ('".$this->_instance."'$par2_query)";
		elseif($action == $this->_act_modify) $query = "UPDATE ".$this->_tbl_options." SET $par_query WHERE instance='$this->_instance'";
		$result = $this->_db->actionquery($query);

		EvtHandler::HttpCall($this->_home, $this->_return_link, "block=options");
	}
}
?>
