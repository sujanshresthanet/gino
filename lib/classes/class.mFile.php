<?php
/**
 * @file class.mFile.php
 * @brief Contiene la classe mFile
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Classe che permette di gestire l'upload multiplo di file
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * REQUISITI
 * ---------------
 * ###Includere il file
 * @code
 * require_once(CLASSES_DIR.OS."class.mFile.php");
 * @endcode
 * 
 * ###Creare una tabella per la gestione dei file
 * Per consuetudine si utilizza come nome della tabella '[riferimento_tbl_classe]_file'.
 * @code
 * CREATE TABLE `base_file` (
 * `id` int(11) NOT NULL auto_increment,
 * `reference` int(11) NOT NULL,
 * `filename` varchar(100) NOT NULL,
 * `description` varchar(200) NOT NULL,
 * PRIMARY KEY  (`id`)
 * ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1 ;
 * @endcode
 * 
 * 
 * OPERAZIONI
 * ---------------
 * ###Visualizzare i file
 * 
 * @code
 * $mfile = new mFile();
 * $options = array('params'=>"opt=3&amp;key=2", 'ajax'=>true);
 * $buffer .= $mfile->mfileList([reference id], [table file], [instance name], [download method], [options]);
 * @endcode
 * 
 * ###Form
 * 
 * @code
 * $mfile = new mFile(array('jsfuncname'=>'fileUpload', 'formname'=>'gform', 'directory'=>$dir, 'div'=>'m_', 'text'=>true));
 * $buffer .= $mfile->jsUploadLib();
 * $options = array();
 * $options1 = array('size'=>20, 'maxlength'=>100, 'extensions'=>$this->_valid_image);	// opzioni di cfile()
 * $options2 = array('size'=>20, 'maxlength'=>200);									// opzioni di cinput()
 * $buffer .= $gform->cell($mfile->mForm('mfile', $this->id ? $this->id : 0, $options, $options1, $options2));
 * @endcode
 * 
 * L'indicazione della directory nell'istanziamento della classe è da inserire nel caso in cui si voglia mostrare 
 * l'elenco dei file associati a un record con link di eliminazione. A questo proposito inseriamo la riga:
 * 
 * @code
 * $buffer .= $gform->cell($mfile->mfileDelList($this->id, $this->_tbl_data."_file", $obj->getInstanceName(), array('view_desc'=>true)));
 * @endcode
 * 
 * ####ALTERNATIVE
 * 
 * al posto di:
 * @code
 * $buffer .= $mfile->jsUploadLib();
 * @endcode
 * 
 * si può cercare di utilizzare (input change):
 * @code
 * $result_div = 'm_file'.$id;
 * $buffer .= $mfile->jsInputLib('attached', 'id', '', '', $result_div, $this->_tbl_base_file);
 * // in questo ultimo caso dovrò richiamare
 * $buffer .= "<div id=\"$result_div\">".$mfile->mFormFromInput($refid, $action, $this->_tbl_base_file, $options, $options1, $options2)."</div>\n";
 * @endcode
 * 
 * ####PER VISUALIZZARE L'ELENCO DEI FILE PRESENTI CON LA POSSIBILITA' DI ELIMINARLI
 * 
 * mfileDelList([id], [table file], [instance name], [opzioni[,[,])
 * 
 * mfileDelList() richiama l'istanza (utilizzando ad es. $obj->getInstanceName()) e, non avendo indicato un metodo specifico,
 * richiama il metodo di default mfileDeleteAttached() che occorre inserire nella classe dell'istanza.
 * 
 * @code
 * public function mfileDeleteAttached() {
 * 	$this->accessGroup('ALL');
 *  $mfile = new mFile();
 * 	$buffer = $mfile->mfileDelAction();	// array('view_desc'=>true)
 * 	return $buffer;
 * }
 * @endcode
 * 
 * mfileDelAction() rimanda a 'mfileDeleteAttached'
 * 
 * ###Action
 * 
 * @code
 * $mfile = new mFile(array('text'=>true));
 * $options = array('chmod_dir'=>0755, 'overwrite'=>1);
 * $upload = $mfile->mAction([input name], [upload dir], [link error], [options]);
 * $mfile->dbUploadAction($upload, [reference id], [table file]);
 * @endcode
 * 
 * ####ALTERNATIVA
 * 
 * @code
 * $mfile = new mFile();
 * $upload = $mfile->mAction([input name], [upload dir], [link error], array('chmod_dir'=>0755, 'overwrite'=>0, 'valid_ext'=>$this->_valid_image));
 * 
 * if(sizeof($upload) > 0)
 * {
 * 	foreach($upload AS $key=>$value)
 * 	{
 * 	 $item = new multimediaItem($id);
 * 	 $item->instance = $this->_instance;
 * 	 $item->image = $key;
 * 	 ...
 * 	 if($item->updateDbData())
 * 	 {
 * 	  $form = new Form('', '', '');
 * 	  $resize = $form->createImage($item->image, $directory, array('prefix_file'=>$this->_prefix_img, 'prefix_thumb'=>$this->_prefix_thumb, 'file_dim'=>$this->_img_width, 'thumb_dim'=>$this->_thumb_width));
 * 	 }
 * 	 else unlink($directory.$item->image);
 * ...
 * @endcode
 * 
 * SCHEMA METODI
 * ---------------
 * jsInputLib()		-> [default] mFormFromInput()
 * mFormFromInput()	-> mForm()
 * mfileDelList()	-> [default] mfileDelAction()
 * 					-> jsDeleteLib() // comprende il js deleteFile($id) che attiva mfileDelAction()
 * 
 * Per modificare il metodo di default indicare nel metodo che lo richiama il nome del metodo ad hoc e della classe relativa
 * 
 * ESEMPIO AJAX
 * ---------------
 * In modo specifico quesrto esempio serve per richiamare il form
 * @code
 * $GINO .= $this->jsLib();
 * $GINO .= "<div id=\"f_attach\"></div>\n";	// style=\"display:none;\" (utilizzando nel js showHide($(result)))
 * $GINO .= "<span onclick=\"fAttach($id, 0, 'f_attach', '".$this->_act_insert."', '', '');\" title=\""._("form allegati")."\">".$this->icon('attach', _("gestione allegati"))."</span>";
 * @endcode
 * 
 * in jsLib():
 * @code
 * 	 $GINO .= "<script type=\"text/javascript\">\n";
 * 	 $GINO .= "function fAttach(ref, id, result, action, method, params) {
 * 	  var output = $(result);
 * 	  var call = '';
 * 	  //showHide(output);
 * 	  if(action == '".$this->_act_delete."') call = 'deleteAttach';
 * 	  else call = 'formAttach';
 * 			
 * 	  var url = '".$this->_home."?pt[".$this->_className."-'+call+']';
 * 	  var data = 'ref='+ref+'&id='+id+'&action='+action+'&div='+result+'&m='+method+'&p='+params;
 * 	  ajaxRequest('post', url, data, result);
 *  };\n";
 * 	$GINO .= "</script>\n";
 * @endcode
 */
class mFile {
	
	private $_home, $_db, $_className;
	private $_form_name, $_dir, $_description;
	private $_js_function_name;
	private $_id_prefix, $_id_name, $_id_hidden_block, $_id_main_block, $_id_print_block;
	private $_current_upload;
	
	private $_base_method, $_base_result;
	private $_options, $_form_label_width, $_form_field_width;
	private $_max_file_size, $_extension_denied;
	private $_act_delete;
	
	/**
	 * Costruttore
	 *
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b jsfuncname (string): nome della funzione javascript
	 *   - @b formname (string): nome del form
	 *   - @b directory (string): percorso assoluto dei file	
	 *   - @b text (boolean): attivare la descrizione del file
	 *   - @b div (string): prefisso per i nomi dei div
	 *   - @b form_label_width
	 *   - @b form_field_width
	 * @return void
	 */
	function __construct($options=null){
		
		$this->_db = db::instance();
		$this->_className = get_class($this);
		
		$this->_form_name = (isset($options['formname']) && $options['formname'] != '') ? $options['formname'] : '';
		$this->_dir = (isset($options['directory']) && $options['directory'] != '') ? $options['directory'] : '';
		$this->_js_function_name = (isset($options['jsfuncname']) && $options['jsfuncname'] != '') ? $options['jsfuncname'] : 'addFileUpload';
		
		$this->_description = isset($options['text']) ? $options['text'] : false;
		$this->_id_prefix = isset($options['div']) ? $options['div'] : '';
		$this->_form_label_width = isset($options['form_label_width'])? $options['form_label_width']:FORM_LABEL_WIDTH;
		$this->_form_field_width = isset($options['form_field_width'])? $options['form_field_width']:FORM_FIELD_WIDTH;
		
		$this->_id_name = $this->_id_prefix.'addupload';
		$this->_id_hidden_block = $this->_id_prefix.'attachment';
		$this->_id_main_block = $this->_id_prefix.'attachments';
		$this->_id_print_block = $this->_id_prefix.'attachmentmarker';
		$this->_current_upload = $this->_id_prefix.'currentUploads';
		
		$this->_base_method = 'mfileDeleteAttached';	// metodo eliminazione file (da creare nella classe istanziante)
		$this->_base_result = 'file_list';	// div elenco file
		
		$this->_max_file_size = MAX_FILE_SIZE;
		$this->_extension_denied = array('php', 'phps', 'js', 'py', 'asp', 'rb', 'cgi', 'cmd', 'sh', 'exe', 'bin');

		$this->_act_delete = 'delete';
	}
	
	private function setOptions($options) {
		$this->_options = $options;
	}

	private function option($opt) {

		return isset($this->_options[$opt]) ? $this->_options[$opt]:null;
	}
	
	private function setDimensions($options) {
		
	}
	
	// Directory finale: $this->_dir.$this->_os.$id.$this->_os
	private function dirUpload($directory){
		
		$directory = (substr($directory, -1) != '/' && $directory != '') ? $directory.'/' : $directory;
		return $directory;
	}
	
	private function pathDirectory($id){
		
		$dir = $this->_dir.$this->_os.$id.$this->_os;
		return $dir;
	}
	
	/**
	 * Rimuove tutti i caratteri ad eccezione di numeri, lettere, _.-, da una stringa, sostituendoli con un underscore
	 * 
	 * @param stringa $filename nome del file
	 * @param string $prefix eventuale testo da aggiungere in testa al nome del file
	 * @return string
	 */
	private function checkFilename($filename, $prefix='') {
	
		$filename = preg_replace("#[^a-zA-Z0-9_\.-]#", "_", $filename);
		return $prefix.$filename;
	}
	
	/**
	 * Visualizzazione dei file caricati
	 * 
	 * E' prevista la possibilità di attivare il loro scaricamento
	 *
	 * @param integer $id reference id
	 * @param string $table table file
	 * @param string $instance instance name
	 * @param string $method download method (es. mdownloader)
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b name_id (string): nome dell'identificatore
	 *   - @b params (string): parametri aggiuntivi (es: opt=3&amp;key=2)
	 *   - @b view_text (boolean): visualizzare la descrizione (default: false)
	 *   - @b ajax (boolean): chiamata 'pt' vs 'evt' (default: false)
	 *   - @b download (boolean): permettere il download dei file (default: true)
	 *   - @b max_char(integer): numero massimo di caratteri del nome del file (viene mostrata l'estensione)
	 * @return string
	 */
	public function mfileList($id, $table, $instance, $method, $options=null){
		
		$this->setOptions($options);
		
		$o_name_id = $this->option('name_id') ? $this->option('name_id') : 'id';
		$o_params = $this->option('params') ? $this->option('params') : '';
		$o_view_text = $this->option('view_text') ? $this->option('view_text') : false;
		$o_ajax = $this->option('ajax') ? $this->option('ajax') : false;
		$o_download = $this->option('download') ? $this->option('download') : true;
		$o_max_char = $this->option('max_char') ? $this->option('max_char') : 0;
		
		$GINO = '';
		
		if(!empty($id))
		{
			$query = "SELECT * FROM $table WHERE reference='$id'";
			$a = $this->_db->selectquery($query);
			if(sizeof($a) > 0)
			{
				$GINO .= "<table class=\"listfile\">";
				
				foreach($a AS $b)
				{
					$fid = $b['id'];
					$filename = htmlChars($b['filename']);
					$description = htmlChars($b['description']);
					
					if($o_max_char > 0) $filename_mod = cutString($filename, $o_max_char, false, true);
					else $filename_mod = $filename;
					
					if(!empty($o_params)) $o_params = "&amp;$o_params";
					$pointer = $o_ajax ? 'pt' : 'evt'; 
					
					if($o_download)
					$link = "<a href=\"".$this->_home."?".$pointer."[$instance-$method]&amp;$o_name_id=$fid".$o_params."\" title=\"$filename\">$filename_mod</a>";
					else $link = $filename;
					
					$GINO .= "<tr>";
					$GINO .= "<td style=\"border-width:0px;\">$link</td>";
					if($o_view_text) $GINO .= "<td style=\"border-width:0px;\">$description</td>";
					$GINO .= "</tr>";
				}
				$GINO .= "</table>";
			}
		}
		
		return $GINO;
	}
	
	/**
	 * Form con passaggio dal metodo jsInputLib()
	 * 
	 * @see mForm()
	 * @param integer $refid
	 * @param string $action
	 * @param string $table
	 * @param array $options
	 * @param array $options_file
	 * @param array $options_text
	 * @return string
	 */
	public function mFormFromInput($refid=0, $action='', $table='', $options=null, $options_file=null, $options_text=null){
		
		$GINO = '';
		
		if(!empty($refid) AND !empty($action) AND !empty($table))
		{
			$id = $refid;
		}
		else	// from jsInputLib()
		{
			$id = cleanVar($_POST, 'rid', 'int', '');
			$action = cleanVar($_POST, 'action', 'string', '');
			$table = cleanVar($_POST, 'tbl', 'string', '');
		}
		
		$GINO .= $this->mForm('mfile', $id, $options, $options_file, $options_text);
		
		return $GINO;
	}
	
	/**
	 * Form
	 * 
	 * @see Form::cinput
	 * @see Form::noinput
	 * @see Form::cfile
	 * 
	 * @param string $name_file			input name
	 * @param integer $id				id di riferimento
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b label_file (string): label del blocco principale (file)
	 *   - @b label_text (string): label del blocco principale (descrizione)
	 *   - @b label_cell (string): label delle righe che vengono create
	 * @param array $options_file opzioni del metodo cfile della classe Form
	 * @param array $options_text opzioni del metodo cinput della classe Form
	 * @param array $options_cell opzioni del metodo noinput della classe Form
	 * @return string
	 */
	public function mForm($name_file, $id=0, $options=null, $options_file=null, $options_text=null, $options_cell=null){
		
		$this->setOptions($options);
		$label_file = $this->option('label_file') ? $this->option('label_file') : _("Aggiungi file");
		$label_text = $this->option('label_text') ? $this->option('label_text') : _("Descrizione");
		$label_cell = $this->option('label_cell') ? $this->option('label_cell') : '';
		
		$name_text = $name_file.'_txt';
		$name_file_array = $name_file.'[]';
		$name_text_array = $name_text.'[]';
		
		$GINO = '';
		
		$gform = new Form($this->_form_name, '', false);
		
		// Blocco nascosto (codice di aggiunta file)
		$file_add = $gform->input($name_file_array, 'file', '', $options_file);
		if($this->_description)
			$file_add .= "<br />".$gform->input($name_text_array, 'text', '', $options_text);
		$file_add .= "&nbsp;<span class=\"link\" onclick=\"javascript:$(this).getParent('div').dispose();\">".pub::icon('delete')."</span>";
		
		$GINO .= "<div id=\"".$this->_id_hidden_block."\" style=\"display:none\">";
		$GINO .= $gform->startTable();
		$GINO .= $gform->noinput($label_cell, $file_add, $options_cell);
		$GINO .= $gform->endTable();
		$GINO .= "</div>";
		// End
		
		// Blocco principale
		$GINO .= "<div id=\"".$this->_id_main_block."\">";
		
		$link_action = "&nbsp;<a id=\"".$this->_id_name."\" href=\"javascript:{$this->_js_function_name}('".$name_file."','".$name_text."','$id')\">".pub::icon('insert')."</a>";
		// window.{$this->_js_function_name}
		
		$GINO .= $gform->startTable();
		$options_file['text_add'] = $link_action;
		$GINO .= $gform->cfile($name_file_array, '', $label_file, $options_file);
		
		if($this->_description)
			$GINO .= $gform->cinput($name_text_array, 'text', '', $label_text, $options_text);
		
		$GINO .= $gform->endTable();
		$GINO .= "<div id=\"".$this->_id_print_block."$id\"></div>";	// stampa del blocco nascosto
		$GINO .= "</div>";
		// End
		
		return $GINO;
	}
	
	/**
	 * Elenco file associati a un record con link di eliminazione
	 * 
	 * @param integer id valore ID del riferimento
	 * @param string table table file
	 * @param string instance nome dell'istanza
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b method (string): nome del metodo da richiamare per eliminare il file
	 *   - @b result (string): div elenco file
	 *   - @b jsfunc (string): nome della funzione javascript che gestisce l'eliminazione dei file (ajaxRequest)
	 *   - @b label (string): label dell'elenco file
	 *   - @b view_desc (boolean): visualizza le descrizioni dei file
	 *   - @b max_chars (integer): numero di caratteri da mostrare nelle descrizione
	 *   - @b td_style (string): stile del tag TD
	 *   - @b label (string): label dell'elenco file
	 * @return string
	 */
	public function mfileDelList($id, $table, $instance, $options=null){
		
		$method = (isset($options['method']) && $options['method'] != '') ? $options['method'] : $this->_base_method;
		$result = (isset($options['result']) && $options['result'] != '') ? $options['result'] : $this->_base_result;
		$jsfunc = (isset($options['jsfunc']) && $options['jsfunc'] != '') ? $options['jsfunc'] : 'deleteFile';
		$label = (isset($options['label']) && $options['label'] != '') ? $options['label'] : _("File associati");
		$view_desc = isset($options['view_desc']) ? $options['view_desc'] : false;
		$max_chars = (isset($options['max_chars']) && is_integer($options['max_chars'])) ? $options['max_chars'] : 0;
		$td_style = isset($options['td_style']) ? $options['td_style'] : '';
		
		if(empty($instance)) return null;
		
		$result = $result.$id;
		
		$GINO = '';
		
		$GINO .= $this->jsDeleteLib($jsfunc, $instance, $method, $result, $table, $this->_dir);
		$GINO .= $this->fileDelList($id, $table, $result, 
		array('jsfunc'=>$jsfunc, 'view_desc'=>$view_desc, 'td_style'=>$td_style, 'label'=>$label, 'max_chars'=>$max_chars));
		
		return $GINO;
	}
	
	private function fileDelList($id, $table, $result, $options=array()){
		
		$jsfunc = array_key_exists('jsfunc', $options) ? $options['jsfunc'] : '';
		$label = (array_key_exists('label', $options) && $options['label'] != '') ? $options['label'] : '';
		$view_desc = array_key_exists('view_desc', $options) ? $options['view_desc'] : false;
		$max_chars = (array_key_exists('max_chars', $options) && is_integer($options['max_chars'])) ? $options['max_chars'] : 0;
		$td_style = (array_key_exists('td_style', $options) && $options['td_style'] != '') ? $options['td_style'] : "border-width:0px;";
		
		$gform = new Form($this->_form_name, '', false);
		
		$GINO = "<div id=\"$result\">";
		$GINO .= $gform->startTable();
		
		if(!empty($id))
		{
			$query = "SELECT * FROM $table WHERE reference='$id'";
			$a = $this->_db->selectquery($query);
			if(sizeof($a) > 0)
			{
				$table = "<table>";
				
				foreach($a AS $b)
				{
					$fid = $b['id'];
					$filename = htmlChars($b['filename']);
					$description = htmlChars($b['description']);
					
					$link_delete = "<span onclick=\"$jsfunc($fid)\" style=\"cursor:pointer; text-decoration:underline; text-align:right\">".pub::icon('delete')."</span>";
					
					$table .= "<tr>";
					$table .= "<td style=\"$td_style\">$filename</td>";
					if($view_desc)
					{
						if($max_chars > 0)
							$description = cutHtmlText($description, $max_chars, '...', true, false, true);
						$table .= "<td style=\"$td_style\">$description</td>";
					}
					$table .= "<td style=\"$td_style\">$link_delete</td>";
					$table .= "</tr>";
				}
				$table .= "</table>";
				
				$GINO .= $gform->noinput($label, $table);
			}
		}
		$GINO .= $gform->endTable();
		$GINO .= "</div>";
		
		return $GINO;
	}
	
	/**
	 * Metodo di default di eliminazione di un file
	 * 
	 * @see pub::deleteFile()
	 * @see mfileDelList()
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b method (string): nome del metodo da richiamare per eliminare il file (di default è il metodo indicato nella proprietà @a _base_method)
	 *   - @b view_desc (boolean)
	 *   - @b td_style (string)
	 * @return string
	 */
	public function mfileDelAction($options=null){
		
		$fid = cleanVar($_POST, 'fid', 'int', '');
		$action = cleanVar($_POST, 'action', 'string', '');
		$instance = cleanVar($_POST, 'inst', 'string', '');
		$jsfunc = cleanVar($_POST, 'func', 'string', '');
		$table = cleanVar($_POST, 'tbl', 'string', '');
		$dir = cleanVar($_POST, 'dir', 'string', '');
		
		$method = (isset($options['method']) && $options['method'] != '') ? $options['method'] : $this->_base_method;
		$view_desc = isset($options['view_desc']) ? $options['view_desc'] : false;
		$td_style = isset($options['td_style']) ? $options['td_style'] : '';
		
		$GINO = '';
		
		if(!empty($fid) AND !empty($table) AND $action == $this->_act_delete)
		{
			if(!is_dir($dir)) return '';
			
			$directory = $this->dirUpload($dir);
			
			$query = "SELECT reference, filename FROM $table WHERE id='$fid'";
			$a = $this->_db->selectquery($query);
			if(sizeof($a) > 0)
			{
				foreach($a AS $b)
				{
					$refid = $b['reference'];
					$filename = $b['filename'];
					
					$pub = new pub();
					$result = $pub->deleteFile($directory.$filename, $this->_home, '', '');
					if($result)
					{
						$query_delete = "DELETE FROM $table WHERE id='$fid'";
						$result_query = $this->_db->actionquery($query_delete);
						
						if(!$result_query)
						$GINO .= "<p>"._("errore nella query di eliminazione del file")." '$filename'</p>";
					}
					else
					{
						$GINO .= "<p>"._("non è stato possibile eliminare il file")." '$filename'</p>";
					}
				}
				$result_div = $this->_base_result.$refid;
				
				$GINO .= $this->mfileDelList($refid, $table, $instance, 
				array('method'=>$method, 'result'=>$result_div, 'jsfunc'=>$jsfunc, 'view_desc'=>$view_desc, 'td_style'=>$td_style));
			}
		}
		
		return $GINO;
	}
	
	/**
	 * Invia al metodo indicato nel parametro $method le variabili: fid, action, inst, func, tbl, dir
	 * 
	 * @param string $jsfunc
	 * @param string $instance
	 * @param string $method
	 * @param string $result
	 * @param string $table
	 * @param string $directory
	 * @return string
	 */
	private function jsDeleteLib($jsfunc, $instance, $method, $result, $table, $directory) {
	
		$GINO = '';
		$GINO .= "<script type=\"text/javascript\">\n";
		$GINO .= "window.$jsfunc=function(id) {
					if(id != null)
					{
						var check=show_alert();
						
						if(check)
						{
							var url = '".$this->_home."?pt[".$instance."-".$method."]';
							var data = 'fid='+id+'&action=".$this->_act_delete."&inst=$instance&func=$jsfunc&tbl=$table&dir=$directory';
							ajaxRequest('post', url, data, '".$result."');
						}
					}
				};
				
				function show_alert(){
				
					var r=confirm(\"sei sicuro?\");
					if (r==true)
  					{
  						return true;
  					}
  					else
  					{
  						return false;
  					}
				};
				
				function show_prompt(){
				
					var nome=prompt(\"Per favore inserisci il tuo nome\",\"Colonnello Kurtz\");
					if (nome!=null && nome!=\"\")
  					{
  						document.write(\"Ciao \" + nome + \"! Bentornato.\");
  					}
				};
				";
		
		$GINO .= "</script>\n";
		
		return $GINO;
	}
	
	/**
	 * Action Upload Multiplo
	 *
	 * @param string $name input name
	 * @param string $dir_upload directory di upload (/path/to/directory/)
	 * @param string $link_error reindirizzamento causa errore
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b overwrite (boolean): true (o 1 per compatibilità)=sovrascrivi i file, false=non sovrascrivere
	 *   - @b max_size (integer): dimensione massima upload in KB
	 *   - @b check_type (boolean): attiva 'types_allowed': true (o 1 per compatibilità)=controlla il tipo di file, false=non controllare
	 *   - @b types_allowed (array): array per alcuni tipi di file (mime types)
	 *   - @b check_denied (boolean): verifica '$this->_extension_denied': true (o 1 per compatibilità)=controlla l'estensione del file, false=non controllare
	 *   - @b debug (integer): 1=stampa informazioni di debug, 0=non stampare
	 *   - @b chmod_dir (integer): permessi della directory (0700)
	 *   - @b valid_ext (array): estensioni valide
	 *   - @b check_name (boolean): verifica i caratteri del nome del file
	 *   - @b resize (boolean): attiva il ridimensionamento di una immagine
	 *   - @b prefix_file (string): nel caso resize=true
	 *   - @b prefix_thumb (string): nel caso resize=true
	 *   - @b width (integer): larghezza in pixel (alternativo al successivo)
	 *   - @b height (integer): altezza in pixel (alternativo al precedente)
	 *   - @b thumb (boolean): attiva la generazione del thumbnail
	 *   - @b t_width (integer): larghezza in pixel del thumbnail (alternativo al successivo)
	 *   - @b t_height (integer): altezza in pixel del thumbnail (alternativo al precedente)
	 * @return array ('filename'=>'description')
	 */
	public function mAction($name, $dir_upload, $link_error, $options=null){
		
		$this->setOptions($options);
		
		$text = $this->_description;
		
		$overwrite = !is_null($this->option('overwrite')) ? $this->option('overwrite') : true;
		$max_size = $this->option('max_size') ? $this->option('max_size') : $this->_max_file_size;
		$check_type = !is_null($this->option('check_type')) ? $this->option('check_type') : true;
		$types_allowed = $this->option('types_allowed') ? $this->option('types_allowed') : 
		array(
			"text/plain",
			"text/html",
			"text/xml",
			"image/jpeg",
			"image/gif",
			"image/png",
			"video/mpeg",
			"audio/midi",
			"application/pdf",
			"application/x-zip-compressed",
			"application/vnd.ms-excel",
			"application/x-msdos-program",
			"application/octet-stream"
		);
		$check_denied = !is_null($this->option('check_denied')) ? $this->option('check_denied') : true;
		$debug = !is_null($this->option('debug')) ? $this->option('debug') : 0;
		$chmod_dir = $this->option('chmod_dir') ? $this->option('chmod_dir') : 0700;
		$valid_ext = $this->option('valid_ext') ? $this->option('valid_ext') : array();
		$check_name = !is_null($this->option('check_name')) ? $this->option('check_name') : true;
		
		$resize = !is_null($this->option('resize')) ? $this->option('resize') : false;
		$prefix_file = !is_null($this->option('prefix_file')) ? $this->option('prefix_file') : '';
		$prefix_thumb = $this->option('prefix_thumb') ? $this->option('prefix_thumb') : null;
		$width = $this->option('width') ? $this->option('width') : null;
		$height = $this->option('height') ? $this->option('height') : null;
		$thumb = !is_null($this->option('thumb')) ? $this->option('thumb') : false;
		$thumb_width = $this->option('t_width') ? $this->option('t_width') : null;
		$thumb_height = $this->option('t_height') ? $this->option('t_height') : null;

		$log = '';
		
		if($text)
		{
			$name_desc = $name.'_txt';
			$description = cleanVar($_POST, $name_desc, 'array', '');
		}
		$dir_upload = $this->dirUpload($dir_upload);
		if(!is_dir($dir_upload)) mkdir($dir_upload, 0755, true);
		
		$data = array();
		$data_txt = array();
		
		// Verifica presenza file
		$number_file = count($_FILES[$name]['tmp_name']);
		
		for($i=0; $i<$number_file; $i++)
		{
			if($_FILES[$name]['size'][$i] == 0)
			{
				$log .= "L'upload del file <strong>{$_FILES[$name]['name'][$i]}</strong> non è andato a buon fine!<br />\n";
				
				unset($_FILES[$name]['name'][$i]);
				unset($_FILES[$name]['type'][$i]);
				unset($_FILES[$name]['size'][$i]);
				unset($_FILES[$name]['error'][$i]);
				unset($_FILES[$name]['tmp_name'][$i]);
			}
		}
		$number_file = count($_FILES[$name]['tmp_name']);
		// End
		
		if(count($_FILES[$name]['name']) > 0)
		{
			$log .= "Hai caricato $number_file file(s)";
			$log .= "<br /><br />\n";
			
			foreach($_FILES[$name]['name'] as $key=>$value)
			{
				if ($debug == 1)
				{
					$log .= "Nome file: <strong>".$_FILES[$name]['name'][$key]."</strong><br />\n";
					$log .= "Tipo file: <strong>".$_FILES[$name]['type'][$key]."</strong><br />\n";
					$log .= "Dimensione: <strong>".$_FILES[$name]['size'][$key]." byte</strong><br />\n";
					$log .= "Nome temporaneo: <strong>".$_FILES[$name]['tmp_name'][$key]."</strong><br />\n";
					if($text) $log .= "Descrizione: <strong>".$description[$key]."</strong><br />\n";
				}
				
				$filename = $check_name ? $this->checkFilename($_FILES[$name]['name'][$key]) : $_FILES[$name]['name'][$key];
				
				if(is_uploaded_file($_FILES[$name]['tmp_name'][$key]))
				{
					if($_FILES[$name]['size'][$key] <= $max_size)
					{
						$finfo = finfo_open(FILEINFO_MIME_TYPE);
						$mime = finfo_file($finfo, $_FILES[$name]['tmp_name'][$key]);
						finfo_close($finfo);
						if(
							((!$check_type || $check_type == 0) || 
							(($check_type || $check_type == 1) && in_array($mime, $types_allowed)))
							&&
							((!$check_denied || $check_denied == 0) || 
							(($check_denied || $check_denied == 1) && !extension($filename, $this->_extension_denied)))
							&&
							(sizeof($valid_ext) == 0 ||
							(sizeof($valid_ext) > 0 && extension($filename, $valid_ext)))
							&&
							!preg_match('#%00#', $filename)
						)
						{
							if(!is_dir($dir_upload) && $dir_upload != '')
							{
								if(!@mkdir($dir_upload, $chmod_dir))
								{
									$log .= "Errore nella creazione della directory <strong>$dir_upload</strong>";
									if($debug == 0)
										exit(error::errorMessage(array('error'=>27), $link_error));
								}
							}
							if(!file_exists($dir_upload.$filename) || $overwrite || $overwrite == 1)
							{
								if(@move_uploaded_file($_FILES[$name]['tmp_name'][$key], $dir_upload.$filename))
								{
									if($resize && ($width || $height))
									{
										if($thumb && ($thumb_width || $thumb_height))
										{
											$t_width = $thumb_width;
											$t_height = $thumb_height;
										}
										else
										{
											$t_width = null;
											$t_height = null;
										}
										
										$form = new Form(null, null, null);
										$form->saveImage($filename, $dir_upload, $prefix_file, $prefix_thumb, $width, $height, $t_width, $t_height);
									}
									
									$log .= "File <strong>{$filename}</strong> trasferito!";
									$data[] = $filename;
									if($text) $data_txt[] = $description[$key]; else $data_txt[] = $filename;
								}
								else
								{
									$log .= "Errore nel trasferimento del file <strong>$filename</strong>";
									if($debug == 0)
										exit(error::errorMessage(array('error'=>28), $link_error));
								}
									
							} else
								$log .= "Il file <strong>$filename</strong> è esistente!";
						} else 
							$log .= "Il tipo di file <strong>".$mime."</strong> non è consentito!";
					} else
						$log .= "La dimensione del file <strong>".$_FILES[$name]['name'][$key]."</strong> supera il limite massimo consentito!";
				}
				else
				{
					$log .= "Errore nel trasferimento del file <strong>$filename</strong>";
					if($debug == 0)
						exit(error::errorMessage(array('error'=>28), $link_error));
				}
				
				$log .= "<hr />\n";
			}
			
			if ($debug == 1) { echo $log; exit(); }
			
			if(sizeof($data) > 0 AND sizeof($data_txt) > 0) $array = array_combine($data, $data_txt);
			else $array = array();
		}
		else $array = array();
		
		return $array;
	}
	
	/**
	 * Inserimento su DB dei risultati dell'upload
	 *
	 * @param array $upload result of mAction()
	 * @param integer $reference reference ID
	 * @param string $table table file
	 * @return void
	 */
	public function dbUploadAction($upload, $reference, $table){
		
		if(sizeof($upload) > 0)
		{
			foreach($upload AS $key=>$value)
			{
				$desc = $this->_description ? $value : '';
				$query = "INSERT INTO $table (reference, filename, description) VALUES ($reference, '$key', '$desc')";
				$result = $this->_db->actionquery($query);
			}
		}
	}
	
	/**
	 * Copia i file uploadati da una directory a un'altra
	 * 
	 * Utile quando un file proveniente da un form deve essere associato a più record e inserito in directory distinte attinenti questi record 
	 * 
	 * @param array $upload output del metodo 'mAction()'
	 * @param string $dir_source directory sorgente (file da copiare)
	 * @param string $dir_dest directory di destinazione
	 * @param string redirect nomeclasse-nomemetodo
	 * @param string $link_error parametri
	 * @return null
	 */
	public function copyUploadedFile($upload, $dir_source, $dir_dest, $redirect, $link_error){
		
		if(sizeof($upload) > 0)
		{
			$dir_source = $this->dirUpload($dir_source);
			$dir_dest = $this->dirUpload($dir_dest);
			
			if(!is_dir($dir_dest) && $dir_dest != '')
			{
				if(!@mkdir($dir_dest, 0755))
					EvtHandler::HttpCall($this->_home, $redirect, $link_error.'error=27');
			}
			
			foreach($upload AS $key=>$value)
			{
				if(file_exists($dir_source.$key))
				{
					if(!@copy($dir_source.$key, $dir_dest.$key))
						EvtHandler::HttpCall($this->_home, $redirect, $link_error.'error=28');
				}
			}
		}
		return null;
	}
	
	/**
	 * Rimuove i file caricati
	 * 
	 * @param string $directory directory dell'upload
	 * @param string $filename nome del file come viene salvato nel record
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b prefix_file (string)
	 *   - @b prefix_thumb (string)
	 * @return null
	 */
	public function removeFile($directory, $filename, $options=null){
		
		$prefix_file = (isset($options['prefix_file']) && !is_null($options['prefix_file'])) ? $options['prefix_file'] : '';
		$prefix_thumb = (isset($options['prefix_thumb']) && !is_null($options['prefix_thumb'])) ? $options['prefix_thumb'] : '';
		
		$directory = $this->dirUpload($directory);
		
		if(file_exists($directory.$prefix_file.$filename))
			unlink($directory.$prefix_file.$filename);
		
		if(file_exists($directory.$prefix_thumb.$filename))
			unlink($directory.$prefix_thumb.$filename);
		
		return null;
	}
	
	/*
		Javascript
	*/
	
	/**
	 * Javascript per presentazione campo multifile a partire da un input
	 * 
	 * Invia alla funzione '$func_name' le variabili: rid, action, tbl, opt
	 * 
	 * @see jsUploadLib()
	 * @param string $input_name nome dell'input di scelta
	 * @param string $ref_id ID di riferimento (input name)
	 * @param string $instance nome istanza
	 * @param string $method nome funzione che recupera i dati
	 * @param string $result nome DIV all'interno del quale vengono scritti i risultati
	 * @param string $table nome della tabella dei file
	 * @return string
	 */
	public function jsInputLib($input_name, $ref_id, $instance='', $method='', $result, $table) {
	
		$GINO = '';
		
		if(empty($instance)) $instance = $this->_className;
		if(empty($method)) $method = 'mfileAttached';
		
		$GINO .= $this->jsUploadLib();
		
		$GINO .= "<script type=\"text/javascript\">\n";
		$GINO .= "window.addEvent('domready', function() {
					
					if($('".$input_name."') != null)
					{
						var myvar;
						
						$('".$input_name."').addEvent('change', function(e) {
							
							myvar = $(this).getProperty('value');
							
							// reimposta l'input
							$(this).setProperty('value', myvar);
							
							ref_id = $('".$ref_id."').getProperty('value');
							action = $('action').getProperty('value');
							table = $('$table').getProperty('value');
							
							var url = '".$this->_home."?pt[$instance-$method]';
							var data = 'rid='+ref_id+'&action='+action+'&tbl='+table+'&opt='+myvar;
							ajaxRequest('post', url, data, '".$result."');
						}
					)
				};
			});\n";
		$GINO .= "</script>\n";
		
		return $GINO;
	}
	
	/**
	 * Costruisce i blocchi contenenti gli input dei file aggiuntivi
	 * @param string $text
	 * @return string
	 */
	public function jsUploadLib($text=''){
		
		if(empty($text)) $text = _("Aggiungi file");
		
		$GINO = "<script type=\"text/javascript\">\n";
		$GINO .= "
var {$this->_current_upload} = 0;	// current # of attachment sections on the web page

// For some reason when a div is taken out, the form will scroll to the top on both Firefox and IE
var scrollPosVert = 0;		// stores the current scroll position on the form

// SCROLL FUNCTIONS
function saveScrollPos(offset){
	scrollPosVert=(document.all)?document.body.scrollTop:window.pageYOffset-offset;
}

function setScrollPos(){
	window.scrollTo(0, scrollPosVert);
	setTimeout('window.scrollTo(0, scrollPosVert)',1);
}

window.".$this->_js_function_name."=function(fileFieldName,descFieldName,refId){
	
	var max = 0;				// maximum # of attachments allowed
	var positionNewAttach = '".$this->_id_print_block."';	// attachmentmarker

	var nameFile = fileFieldName;
	var nameDesc = descFieldName;
	{$this->_current_upload}++;
	
	if(refId != '')
		positionAttachment=positionNewAttach+refId;
	
	if ({$this->_current_upload}>0)
		document.getElementById('".$this->_id_name."').childNodes[0].data='$text';
	
	// First, clone the hidden attachment section
	var newFields = document.getElementById('".$this->_id_hidden_block."').cloneNode(true);	// attachment
	newFields.id = '';
	// Make the new attachments section visible
	newFields.style.display = 'block';
	
	// loop through tags in the new Attachment section and set ID and NAME properties
	var newField = newFields.childNodes;
	for (var i=0;i<newField.length;i++)
	{
		if (newField[i].name==nameFile)
		{
			newField[i].id=nameFile+{$this->_current_upload};
			newField[i].name=nameFile+'[]';
			//newField[i].name=nameFile+{$this->_current_upload};
		}
		if (newField[i].name==nameDesc)
		{
			newField[i].id=nameDesc+{$this->_current_upload};
			newField[i].name=nameDesc+'[]';
			//newField[i].name=nameDesc+{$this->_current_upload};
		}
	}
	// Insert our new Attachment section into the Attachments Div on the form...
	var insertHere = document.getElementById(positionAttachment);
	insertHere.parentNode.insertBefore(newFields,insertHere);
}

// This function removes an attachment from the form
// and updates the ID and Name properties of all other
// Attachment sections
function removeFile(container, item){
	// get the ID number of the upload section to remove
	var tmp = item.getElementsByTagName('input')[0];
	var basefieldname = '';
	basefieldname = nameFile;
	var iRemove=Number(tmp.id.substring(basefieldname.length, tmp.id.length));
	// Shift all INPUT field IDs and NAMEs down by one (for fields with a higher ID than the one being removed)
	var x = document.getElementById('".$this->_id_main_block."').getElementsByTagName('input');
	for (i=0;i<x.length;i++){
		basefieldname=nameFile;
		var iEdit = Number(x[i].id.substring(basefieldname.length, x[i].id.length));
		if (iEdit>iRemove){
			x[i].id=basefieldname+(iEdit-1);
			x[i].name=basefieldname+(iEdit-1);
		}
	}

	// Run through all the DropCap divs (the number to the right of the attachment section) and update that number...
	x=document.getElementById('".$this->_id_main_block."').getElementsByTagName('div');
	for (i=0;i<x.length;i++){
		// Verify this is actually the \"dropcap\" div
		if (x[i].id.substring(0, String('dropcap').length)=='dropcap'){
			ID = Number(x[i].id.substring(String('dropcap').length, x[i].id.length));
			// check to see if current attachment had a higher ID than the one we're
			// removing (and thus needs to have its ID dropped)
			if (ID>iRemove){
				x[i].id='dropcap'+(ID-1);
				x[i].childNodes[0].data=(ID-1);
			}
		}
	}

	{$this->_current_upload}--;
	saveScrollPos(0);
	container.removeChild(item);
	setScrollPos();
	document.getElementById('".$this->_id_name."').style.visibility='visible';
	if ({$this->_current_upload}==0)
		document.getElementById('".$this->_id_name."').childNodes[0].data='$text';
}
		";
		$GINO .= "</script>\n";
		
		return $GINO;
	}
}
?>
