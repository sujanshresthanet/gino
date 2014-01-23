<?php
/**
 * @file class.template.php
 * @brief Contiene la classe Template
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Libreria per la gestione dei template
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Template extends Model {
	
	protected $_tbl_data;
	private static $_tbl_tpl = 'sys_layout_tpl';
	private static $_tbl_tpl_block = 'sys_layout_tpl_block';
	private $_home, $_interface;

	private $_blocks_number, $_blocks_properties;
	private $_align_dict;
	private $_um_dict;

	/**
	 * Costruttore
	 * 
	 * @param integer $id valore ID del record
	 * @return void
	 */
	function __construct($id) {

		$this->_tbl_data = self::$_tbl_tpl;

		parent::__construct($id);

		$this->_home = 'index.php';
		$this->_interface = 'layout';

		$this->initBlocksProperties();

		$this->_align_dict = array("1"=>"sinistra", "2"=>"centro", "3"=>"destra");
		$this->_um_dict = array("1"=>"px", "2"=>"%");

	}
	
	private function initBlocksProperties() {
	
		$this->_blocks_properties = array();	
		if(!$this->id) $this->_blocks_number = 0;
		else {
			$query = "SELECT COUNT(id) as tot FROM ".self::$_tbl_tpl_block." WHERE tpl='$this->id'";
			$a = $this->_db->selectquery($query);
			if(sizeof($a)>0) $this->_blocks_number = $a[0]['tot'];
			else $this->_blocks_number = 0;
		}

		$query = "SELECT id, position, width, um, align, rows, cols FROM ".self::$_tbl_tpl_block." WHERE tpl='$this->id' ORDER BY position ASC";
		$a = $this->_db->selectquery($query);
		if(sizeof($a)>0) {
			foreach($a as $b) {
				$this->_blocks_properties[$b['position']] = array(
						"id"=>$b['id'],
						"width"=>$b['width'],
						"um"=>$b['um'],
						"align"=>$b['align'],
						"rows"=>$b['rows'],
						"cols"=>$b['cols']
				);
			}
		}
	}
	
	/**
	 * Imposta il valore per la query di inserimento e modifica del record
	 * 
	 * @param string $value nome del file dei template
	 * @return boolean
	 */
	public function setFilename($value) {
		
		if($this->_p['filename']!=$value && !in_array('filename', $this->_chgP)) $this->_chgP[] = 'filename';
		$this->_p['filename'] = $value;

		return true;
	}

	/**
	 * Elenco dei template in formato object
	 * 
	 * @param string $order per quale campo ordinare i risultati
	 * @return array
	 */
	public static function getAll($order='label') {

		$db = db::instance();
		$res = array();
		$query = "SELECT id, label, filename, description FROM ".self::$_tbl_tpl." ORDER BY $order";
		$a = $db->selectquery($query);
		if(sizeof($a)>0) {
			foreach($a as $b) {
				$res[] = new template($b['id']);
			}
		}

		return $res;
	}
	
	/**
	 * Descrizione della procedura
	 * 
	 * @return string
	 */
	public static function layoutInfo() {

    $buffer = "<h2>"._('Template')."</h2>";
		$buffer .= "<p>"._("gino supporta la creazione di tipi differenti di template, è possibile creare template a blocchi utilizzando il motorino di template apposito, oppure template liberi scrivendo direttamente codice php. Il template creato dovrà poi essere associato ad una skin per essere renderizzato secondo le regole definite dalla skin stessa.");
		$buffer .= "<h3>"._("Template a blocchi")."</h3>\n";
		$buffer .= "<p>"._("La struttura del template è formata da blocchi che contengono navate. Ciascuna navata può contenere un numero qualsiasi di moduli. I moduli lasciati 'vuoti' non occuperanno spazio all'interno del layout finale, mentre le navate 'vuote' occuperanno lo spazio in larghezza esattamente come definito nel template.")."</p>\n";
		$buffer .= "<p>"._("E' possibile inserire qualunque vista esportata dai moduli e la vista corrente (quella specifica dell'url visitato). Il dimensionamento di blocchi e navate può essere gestito in px oppure in percentuali. L'intestazione del documento html non è controllabile, ma viene interamente gestita da gino.")."</p>\n";
		$buffer .= "<p>"._("Nella maschera di modifica e inserimento è presente il campo 'css' nel quale si può specificare un foglio di stile che viene caricato nella maschera di creazione del template. Selezionando un file css, il foglio di stile non viene automaticamente associato al template, cosa che deve essere fatta al momento di creazione della skin, ma viene utilizzato per creare un template adatto se si ha in previsione di utilizzarlo all'interno di una skin con un css che modifichi le dimensioni degli elementi strutturali.")."</p>\n";
		$buffer .= "<h3>"._("Template libero")."</h3>\n";
		$buffer .= "<p>"._("Creando un template libero è possibile controllare finemente ogni aspetto del layout finale della pagina. Il template comprende l'intero documento, dalla definizione del DOCTYPE alla chiusura del tag html. E' possibile utilizzare codice php, si hanno a disposizione tutte le librerie di GINO. In questo caso non è necessario associare fogli di stile caricati a proposito, in quanto si possono direttamente controllare le chiamate a css, javascript etc... modificando l'intestazione del documento.")."</p>\n";

    return $buffer;
	}
	
	private function formData($gform, $free = false) {
		
    if($free) {
      $formaction = $this->_home."?evt[".$this->_interface."-actionTemplate]&free=1";
    }
    else {
      $formaction = $this->_home."?pt[".$this->_interface."-manageLayout]&block=template&action=mngtpl";
    }
		$required = 'label';
		$buffer = $gform->open($formaction, '', $required);
		$buffer .= $gform->hidden('id', $this->id);
		$buffer .= $gform->cinput('label', 'text', $gform->retvar('label', htmlInput($this->label)), _("Etichetta"), array("required"=>true, "size"=>40, "maxlength"=>200, "trnsl"=>true, "field"=>"label"));
		$buffer .= ($this->id)
			? $gform->cinput('filename', 'text', htmlInput($this->filename), _("Nome file"), array("other"=>"disabled", "size"=>40, "maxlength"=>200))
			: $gform->cinput('filename', 'text', $gform->retvar('filename', htmlInput($this->filename)), array(_("Nome file"), _("Senza estensione, es. home_page")), array("required"=>true, "size"=>40, "maxlength"=>200, "pattern"=>"^[\d\w_-]*$", "hint"=>_("caratteri alfanumerici, '_', '-'")));
		$buffer .= $gform->ctextarea('description', $gform->retvar('description', htmlInput($this->description)), _("Descrizione"), array("cols"=>45, "rows"=>4, "trnsl"=>true, "field"=>"description"));

    if(!$free) {
      $css_list = array();
      foreach(css::getAll() as $css) {
        $css_list[$css->id] = htmlInput($css->label);
      }
      $buffer .= $gform->cselect('css', $gform->retvar('css', $this->css), $css_list, array(_("Css"), _("Selezionare il css qualora lo si voglia associare al template nel momento di definizione della skin (utile per la visualizzazione delle anteprime nello schema)")), null);
    }

		return $buffer;
	}

  public function formFreeTemplate() {

    $registry = registry::instance();
		$registry->addJs(SITE_JS."/CodeMirror/codemirror.js");
		$registry->addCss(CSS_WWW."/codemirror.css");

    $registry->addJs(SITE_JS."/CodeMirror/htmlmixed.js");
    $registry->addJs(SITE_JS."/CodeMirror/matchbrackets.js");
    $registry->addJs(SITE_JS."/CodeMirror/css.js");
    $registry->addJs(SITE_JS."/CodeMirror/xml.js");
    $registry->addJs(SITE_JS."/CodeMirror/clike.js");
    $registry->addJs(SITE_JS."/CodeMirror/php.js");
    $options = "{
      lineNumbers: true,
      matchBrackets: true,
      mode: \"application/x-httpd-php\",
      indentUnit: 4,
      indentWithTabs: true,
      enterMode: \"keep\",
      tabMode: \"shift\"
    }";

    if($this->id) {
      $code = file_get_contents(TPL_DIR.OS.$this->filename);
    } 
    else {
      $code = file_get_contents(TPL_DIR.OS."default_free_tpl.php");
    }
  
		$gform = Loader::load('Form', array('gform', 'post', true, array("trnsl_table"=>$this->_tbl_data, "trnsl_id"=>$this->id)));
		$gform->load('dataform');

    $title = ($this->id) ? _("Modifica template")." '".htmlChars($this->label)."'" : _("Nuovo template");

    $buffer = "<div class=\"backoffice-info\">";
    $buffer .= "<p>"._('La scrittura di template in modalità libera consente di scrivere direttamente il template utilizzando codice php. E\' uno strumento molto potente quanto pericoloso, si consiglia di non modificare template amministrativi in questo modo, in quanto se dovessero verificarsi degli errori non sarebbe in alcuni casi possibile correggerli.')."</p>";
    $buffer .= "<p>"._('Tutte le classi di GINO sono disponibili attraverso il modulo Loader, ed il registro $register è già disponibile. Consultare le reference di GINO per maggiori informazioni.')."</p>";
    $buffer .= "<p>".sprintf(_('Le viste disponibili sono inseribili all\'interno del template utilizzando una particolare sintassi. <span class="link" onclick="%s">CLICCA QUI</span> per ottenere un elenco.'), "var w = new gino.layerWindow({
      'title': '"._('Moduli e pagine')."',
        'url': '".$this->_home."?pt[".$this->_interface."-modulesCodeList]',
        'width': 800,
        height: 500
    }); w.display();")."</p>";
    $buffer .= "</div>";

		$buffer .= $this->formData($gform, true);
		$buffer .= $gform->hidden('free', 1);
		$buffer .= $gform->ctextarea('code', $gform->retvar('code', $code), _("Codice PHP"), array("cols"=>45, "rows"=>14, 'id'=>'codemirror'));
    $save_and_continue = $gform->input('savecontinue_action', 'submit', _('salva e continua la modifica'), array("classField"=>"submit"));
		$buffer .= $gform->cinput('submit_action', 'submit', _('salva'), '', array("classField"=>"submit", 'text_add'=>$save_and_continue));
		$buffer .= $gform->close();

    $buffer .= "<script>var myCodeMirror = CodeMirror.fromTextArea(document.getElementById('codemirror'), $options);</script>";

    $view = new view();
    $view->setViewTpl('section');
    $dict = array(
      'title' => $title,
      'class' => 'admin',
      'content' => $buffer
    );

    return $view->render($dict);
  }

  public function actionFreeTemplate() {
    $this->free = 1;
		$this->label = cleanVar($_POST, 'label', 'string', '');
		$this->description = cleanVar($_POST, 'description', 'string', '');
		$tplFilename = cleanVar($_POST, 'filename', 'string', '');
		if($tplFilename) $this->filename = $tplFilename.".php";

		$action = ($this->id)? "modify" : "insert";

		$link_error = $this->_home."?evt[$this->_interface-manageLayout]&block=template&action=$action&free=1";

		if(!$this->id && is_file(TPL_DIR.OS.$this->filename.".php")) 
			exit(error::errorMessage(array('error'=>_("Nome file già presente")), $link_error));
		
		if($fp = @fopen(TPL_DIR.OS.$this->filename, "wb")) {
		  $code = filter_input(INPUT_POST, 'code');
			fwrite($fp, $code) || exit(error::errorMessage(array('error'=>_("Impossibile scrivere il file")), $link_error));
			fclose($fp);
		}
		else exit(error::errorMessage(array('error'=>_("Impossibile creare il file"), 'hint'=>_("Controllare i permessi in scrittura all'interno della cartella ".TPL_DIR.OS)), $link_error));
		
		$this->updateDbData();

    if(isset($_POST['savecontinue_action'])) {
      header("Location: $this->_home?evt[$this->_interface-manageLayout]&block=template&id=".$this->id."&action=modify&free=1");
    }
    else {
      header("Location: $this->_home?evt[$this->_interface-manageLayout]&block=template");
    }
    exit();
  
  }

	/**
	 * Form per la creazione e la modifica di un template
	 * 
	 * @see formBlock()
	 * @return string
	 */
	public function formTemplate() {

		$gform = Loader::load('Form', array('gform', 'post', true, array("trnsl_table"=>$this->_tbl_data, "trnsl_id"=>$this->id)));
		$gform->load('dataform');

    $title = ($this->id) ? _("Modifica template")." '".htmlChars($this->label)."'" : _("Nuovo template");

		$buffer = $this->formData($gform);
		if($this->id)
			$buffer .= $gform->hidden('modTpl', 1);
		$buffer .= $this->formBlock($gform);
		$buffer .= $gform->cinput('submit_action', 'submit', (($this->id)?_("procedi con la modifica del template"):_("crea template")), '', array("classField"=>"submit"));
		$buffer .= $gform->close();

    $view = new view();
    $view->setViewTpl('section');
    $dict = array(
      'title' => $title,
      'class' => 'admin',
      'content' => $buffer
    );

    return $view->render($dict);

	}
	
	/**
	 * Form che introduce alla modifica dello schema
	 * 
	 * @return string
	 */
	public function formOutline() {

		if(!$this->id) return null;
		
		$gform = Loader::load('Form', array('gform', 'post', true, array("trnsl_table"=>$this->_tbl_data, "trnsl_id"=>$this->id)));
		$gform->load('dataform');

		$title = _("Modifica lo schema");

		$buffer = $this->formData($gform);
		$buffer .= $gform->cinput('blocks_number', 'text', $this->_blocks_number, _('numero blocchi'), array("other"=>"disabled", 'size'=>1));
		$buffer .= $gform->cinput('submit_action', 'submit', _("vai allo schema"), '', array("classField"=>"submit"));
		$buffer .= $gform->close();

    $view = new view();
    $view->setViewTpl('section');
    $dict = array(
      'title' => $title,
      'class' => 'admin',
      'content' => $buffer
    );

    return $view->render($dict);
	}
	
	/**
	 * Form di duplicazione di un template
	 * 
	 * @return string
	 */
	public function formCopyTemplate() {

		$gform = Loader::load('Form', array('gform', 'post', true));
		$gform->load('dataform');

		$title = sprintf(_('Duplica template "%s"'), htmlChars($this->label));

		$required = 'label,filename';
		$buffer = $gform->open($this->_home."?evt[".$this->_interface."-manageLayout]&block=template&action=copytpl", '', $required);
		$buffer .= $gform->hidden('ref', $this->id);
		$buffer .= $gform->cinput('label', 'text', $gform->retvar('label', ''), _("Etichetta"), array("required"=>true, "size"=>40, "maxlength"=>200));
		$buffer .= $gform->cinput('filename', 'text', $gform->retvar('filename', ''), array(_("Nome file"), _("Senza estensione, es. home_page")), array("required"=>true, "size"=>40, "maxlength"=>200, "pattern"=>"^[\d\w_-]*$", "hint"=>_("caratteri alfanumerici, '_', '-'")));
		$buffer .= $gform->ctextarea('description', $gform->retvar('description', ''), _("Descrizione"), array("cols"=>45, "rows"=>4));
		$buffer .= $gform->cinput('submit_action', 'submit', _("crea template"), '', array("classField"=>"submit"));

		$buffer .= $gform->close();

    $view = new view();
    $view->setViewTpl('section');
    $dict = array(
      'title' => $title,
      'class' => 'admin',
      'content' => $buffer
    );

    return $view->render($dict);
	}

	/**
	 * Opera una scelta sul come e se mostrare i blocchi del template nel caso si tratti di un nuovo template o di modificarne uno
	 * 
	 * @see tplBlockForm()
	 * @param object $gform oggetto della classe Form
	 * @return string
	 */
	private function formBlock($gform) {
	
		if($this->id) {
			
			$buffer = $gform->cinput('blocks', 'text', $this->_blocks_number, _("Numero blocchi"), array());
			$buffer .= $gform->hidden('blocks_number', $this->_blocks_number);
			$buffer .= "<div id=\"blocks_form\">".$this->tplBlockForm()."</div>";
		}
		else {
			for($i=1, $blocks_list=array(); $i<11; $i++) $blocks_list[$i] = $i;

			$onchange = "onchange=\"gino.ajaxRequest('post', '$this->_home?pt[layout-manageLayout]&block=template&action=mngblocks', 'id=$this->id&blocks_number='+$(this).value, 'blocks_form', {'load':'blocks_form'});\"";
			$buffer = $gform->cselect('blocks_number', $gform->retvar('blocks_number', $this->_blocks_number), $blocks_list, array(_("Numero blocchi"), _("Selezionare il numero di blocchi che devono comporre il layout")), array("js"=>$onchange));
			$buffer .= "<div id=\"blocks_form\"></div>";
		}

		return $buffer;
	}
	
	/**
	 * Stampa i blocchi del template
	 * 
	 * @return string
	 */
	public function tplBlockForm() {
	
		$gform = Loader::load('Form', array('gform', 'post', false));

		$blocks_number = $this->id ? $this->_blocks_number : cleanVar($_POST, 'blocks_number', 'int', '');
		
		$buffer = '';
		
		if($this->id)
		{
			$note = "<p class=\"backoffice-info\">"._("ATTENZIONE: l'aggiunta o l'eliminazione anche soltanto di un blocco può comportare la necessità di rimettere mano alle classi del <b>CSS</b>, in quanto cambia 
			la sequenza dei blocchi e quindi il nome di riferimento alla classe del CSS.")."</p>";
			$buffer .= $note;
		}
		for($i=1; $i<$blocks_number+1; $i++) {

			if($this->id)
			{
				$name_select = 'addblocks_'.$i;
				$div_id = 'addblocks_form'.$i;
				$onchange = "onchange=\"gino.ajaxRequest('post', '$this->_home?pt[layout-manageLayout]&block=template&action=addblocks', 'id=$this->id&ref=$i&$name_select='+$(this).value, '$div_id', {'load':'$div_id'});\"";
				$test_add = $gform->cselect($name_select, '', array(1=>1, 2=>2), _('Numero blocchi da aggiungere'), array("js"=>$onchange));
				$buffer .= $test_add;
				
				$buffer .= "<div id=\"$div_id\">";
				$buffer .= $this->addBlockForm($i);
				$buffer .= "</div>";
			}
			
			$buffer .= "<fieldset id=\"block$i\">";

      $moo = "
      var getStatus = $('block$i').getStyle('opacity');
      if(getStatus == '0.2') {
        $('block$i').setStyle('opacity', '1');
        $('block$i').setStyle('color', '#333');
        $('del$i').value = 0;
        $('block$i').getElements('input, select').each(function(el) { el.removeProperty('disabled'); });
      }
      else {
        $('block$i').setStyle('opacity', '0.2');
        $('block$i').setStyle('color', '#FFF');
        $('del$i').value = 1;
        $('block$i').getElements('input, select').each(function(el) { el.setProperty('disabled', 'disabled'); });
      };";

			$text_block = "<legend>"._("Blocco")." $i <span onclick=\"$moo\" class=\"pull-right\" style=\"cursor: pointer\">".pub::icon('delete')."</span></legend>";
			
			if($this->id) {
				
				$text_block .= $gform->hidden('del'.$i, 0, array('id'=>'del'.$i));
				$buffer .= $text_block;
				
				$buffer .= $gform->hidden('id_'.$i, $this->_blocks_properties[$i]['id']);
				
				$width = $this->_blocks_properties[$i]['width'] ? $this->_blocks_properties[$i]['width'] : '';
				
				$um = " ".$gform->select('um_'.$i, $this->_blocks_properties[$i]['um'], $this->_um_dict, array());
				$buffer .= $gform->cinput('width_'.$i, 'text', $width, array(_("Larghezza"), _("Se non specificata occupa tutto lo spazio disponibile")), array("required"=>false, "size"=>4, "maxlength"=>4, "text_add"=>$um));
				
				$buffer .= $gform->cselect('align_'.$i, $this->_blocks_properties[$i]['align'], $this->_align_dict, _("Allineamento"), array());
				
				$buffer .= $gform->cinput('rows_'.$i, 'text', $this->_blocks_properties[$i]['rows'], _("Numero righe"), array("required"=>true, "size"=>2, "maxlength"=>2));
				
				$buffer .= $gform->cinput('cols_'.$i, 'text', $this->_blocks_properties[$i]['cols'], _("Numero colonne"), array("required"=>true, "size"=>2, "maxlength"=>2));
			}
			else {
				
				$buffer .= $text_block;
				
				$um = " ".$gform->select('um_'.$i, '', $this->_um_dict, array());
				$buffer .= $gform->cinput('width_'.$i, 'text', '', array(_("Larghezza"), _("Se non specificata occupa tutto lo spazio disponibile")), array("required"=>false, "size"=>4, "maxlength"=>4, "text_add"=>$um));
				$buffer .= $gform->cselect('align_'.$i, '', $this->_align_dict, _("Allineamento"), array());
				$buffer .= $gform->cinput('rows_'.$i, 'text', '', _("Numero righe"), array("required"=>true, "size"=>2, "maxlength"=>2));
				$buffer .= $gform->cinput('cols_'.$i, 'text', '', _("Numero colonne"), array("required"=>true, "size"=>2, "maxlength"=>2));
			}
			$buffer .= "</fieldset>";
		}

		return $buffer;
	}
	
	/**
	 * Stampa i blocchi che vogliono essere aggiunti nel template
	 * 
	 * @param integer $ref numero del blocco nella sequenza corretta
	 * @return string
	 */
	public function addBlockForm($ref=null) {
		
		if(is_null($ref)) $ref = cleanVar($_POST, 'ref', 'int', '');
		if(!$ref) return null;
		
		$gform = Loader::load('Form', array('gform', 'post', false));
		
		$buffer = '';
		
		$add_num = cleanVar($_POST, 'addblocks_'.$ref, 'int', '');
		$buffer .= $gform->hidden('addblocks_'.$ref, $add_num);
		
		for($i=1; $i<$add_num+1; $i++) {
			
			$ref_name = $ref.'_'.$i;
      $buffer .= "<fieldset>";
      $buffer .= "<legend>"._('Nuovo blocco')."</legend>";
			$um = " ".$gform->select('um_add'.$ref_name, '', $this->_um_dict, array());
			$buffer .= $gform->cinput('width_add'.$ref_name, 'text', '', array(_("Larghezza"), _("Se non specificata occupa tutto lo spazio disponibile")), array("required"=>false, "size"=>4, "maxlength"=>4, "text_add"=>$um));
			$buffer .= $gform->cselect('align_add'.$ref_name, '', $this->_align_dict, _("Allineamento"), array());
			$buffer .= $gform->cinput('rows_add'.$ref_name, 'text', '', _("Numero righe"), array("required"=>true, "size"=>2, "maxlength"=>2));
			$buffer .= $gform->cinput('cols_add'.$ref_name, 'text', '', _("Numero colonne"), array("required"=>true, "size"=>2, "maxlength"=>2));
      $buffer .= "</fieldset>";
		}
		
		return $buffer;
	}
	
	/**
	 * Form di eliminazione di un template
	 * 
	 * @return string
	 */
	public function formDelTemplate() {
	
		$gform = Loader::load('Form', array('gform', 'post', true));
		$gform->load('dataform');

    $buffer = "<p class=\"backoffice-info\">"._("L'eliminazione di un template determina l'eliminazione del template dalle skin che lo contengono!")."</p>";
		$required = '';
		$buffer .= $gform->open($this->_home."?evt[".$this->_interface."-actionDelTemplate]", '', $required);
		$buffer .= $gform->hidden('id', $this->id);
		$buffer .= $gform->cinput('submit_action', 'submit', _("elimina"), _("Sicuro di voler procedere?"), array("classField"=>"submit"));
		$buffer .= $gform->close();

    $view = new view();
    $view->setViewTpl('section');
    $dict = array(
      'title' => sprintf(_('Elimina template "%s"'), htmlChars($this->label)),
      'class' => 'admin',
      'content' => $buffer
    );
    
    return $view->render($dict);
	}
	
	/**
	 * Eliminazione di un template
	 * 
	 * @see skin::removeTemplate()
	 */
	public function actionDelTemplate() {

    loader::import('class', 'Skin');

		if($this->filename) @unlink(TPL_DIR.OS.$this->filename);		

		skin::removeTemplate($this->id);

		$this->_registry->trd->deleteTranslations($this->_tbl_data, $this->id);
    if(!$this->free) {
      $this->deleteBlocks();
    }
		$this->deleteDbData();

		header("Location: $this->_home?evt[$this->_interface-manageLayout]&block=template");
	}

	/**
	 * Stampa lo schema del template
	 * 
	 * La creazione e la ricostruzione del template sono i due casi in cui si creano e si modificano i blocchi.
	 * Il metodo che lavora sui blocchi è createTemplate(); nel caso della modifica del template viene letto direttamente il file.
	 * 
	 * @see renderNave()
	 * @param object $css istanza della classe css
	 * @param integer $tpl_id valore ID del template
	 * @return string
	 */
	public function manageTemplate($css, $tpl_id=0) {

		$gform = Loader::load('Form', array('tplform', 'post', false, array("tblLayout"=>false)));
		$gform->load('dataform');

		$modTpl = cleanVar($_POST, 'modTpl', 'int', '');	// parametro di ricostruzione del template
		$label = cleanVar($_POST, 'label', 'string', '');
		$filename = cleanVar($_POST, 'filename', 'string', '');
		$description = cleanVar($_POST, 'description', 'string', '');
		$blocks_number = cleanVar($_POST, 'blocks_number', 'int', '');

		if($this->id) {
			$template = $this->filename;
			$template = file_get_contents(TPL_DIR.OS.$template);
			
			if($modTpl)
				$template = $this->createTemplate($blocks_number, $template);
		}
		else $template = $this->createTemplate($blocks_number);	// ricostruzione del template
		
		$buffer = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n";
		$buffer .= "<html xmlns=\"http://www.w3.org/1999/xhtml\" lang=\"en\" xml:lang=\"en\">\n";
		$buffer .= "<head>\n";
		$buffer .= "<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\" />\n";
		$buffer .= "<title>Template</title>\n";
		
		$buffer .= "<link rel=\"stylesheet\" href=\"".CSS_WWW."/styles.css\" type=\"text/css\" />\n";
		$buffer .= "<link rel=\"stylesheet\" href=\"".SITE_APP.OS."layout".OS."layout.css\" type=\"text/css\" />\n";
		if($css->id)
			$buffer .= "<link rel=\"stylesheet\" href=\"".CSS_WWW."/$css->filename\" type=\"text/css\" />\n";
		
		$buffer .= "<script type=\"text/javascript\" src=\"".SITE_JS."/mootools-1.4.0-yc.js\"></script>\n";
		$buffer .= "<script type=\"text/javascript\" src=\"".SITE_JS."/gino-min.js\"></script>\n";
		$buffer .= "<script type=\"text/javascript\" src=\"".SITE_APP."/layout/layout.js\"></script>\n";
		$buffer .= "</head>\n";

		$buffer .= "<body>\n";
		$buffer .= "<p class=\"title\">$label</p>";

		$regexp = "/(<div(?:.*?)(id=\"(nav_.*?)\")(?:.*?)>)\n?([^<>]*?)\n?(<\/div>)/";
		$render = preg_replace_callback($regexp, array($this, "renderNave"), $template);
		$buffer .= $render;
		
		// Form
		$required = '';
		$buffer .= $gform->open($this->_home."?evt[".$this->_interface."-actionTemplate]", '', $required);
		$buffer .= $gform->hidden('id', $this->id);
		$buffer .= $gform->hidden('label', htmlInput($label));
		$buffer .= $gform->hidden('description', htmlInput($description));
		$buffer .= $gform->hidden('filename', $filename);
		$buffer .= $gform->hidden('selMdlTitle', _("Selezione modulo"), array("id"=>"selMdlTitle"));
		$buffer .= $gform->hidden('tplform_text', '', array("id"=>"tplform_text"));
		
		if(!$this->id || ($this->id && $modTpl))
		{
			if($modTpl)
				$buffer .= $gform->hidden('modTpl', $modTpl);
			
			$blocks_del = array();
			$num = 1;
			for($i=1; $i<=$blocks_number; $i++)
			{
				$add_form = cleanVar($_POST, 'addblocks_'.$i, 'int', '');
				for($y=1; $y<=$add_form; $y++) {
					
					$ref_name = $i.'_'.$y;
					
					$buffer .= $gform->hidden('id_'.$num, 0);
					$buffer .= $gform->hidden('width_'.$num, cleanVar($_POST, 'width_add'.$ref_name, 'int', ''));
					$buffer .= $gform->hidden('um_'.$num, cleanVar($_POST, 'um_add'.$ref_name, 'int', ''));
					$buffer .= $gform->hidden('align_'.$num, cleanVar($_POST, 'align_add'.$ref_name, 'int', ''));
					$buffer .= $gform->hidden('rows_'.$num, cleanVar($_POST, 'rows_add'.$ref_name, 'int', ''));
					$buffer .= $gform->hidden('cols_'.$num, cleanVar($_POST, 'cols_add'.$ref_name, 'int', ''));
					$num++;
				}
				
				$id_block = cleanVar($_POST, 'id_'.$i, 'int', '');
				$del_block = cleanVar($_POST, 'del'.$i, 'int', '');
				
				$buffer .= $gform->hidden('id_'.$num, $id_block);
				$buffer .= $gform->hidden('width_'.$num, cleanVar($_POST, 'width_'.$i, 'int', ''));
				$buffer .= $gform->hidden('um_'.$num, cleanVar($_POST, 'um_'.$i, 'int', ''));
				$buffer .= $gform->hidden('align_'.$num, cleanVar($_POST, 'align_'.$i, 'int', ''));
				$buffer .= $gform->hidden('rows_'.$num, cleanVar($_POST, 'rows_'.$i, 'int', ''));
				$buffer .= $gform->hidden('cols_'.$num, cleanVar($_POST, 'cols_'.$i, 'int', ''));
				
				if($del_block == 1)
					$blocks_del[$id_block] = $i;
				else
					$num++;
			}
			$buffer .= $gform->hidden('blocks_number', $num-1);
			$buffer .= $gform->hidden('blocks_del', base64_encode(json_encode($blocks_del)));
		}
		$buffer .= $gform->input('back', 'button', _("indietro"), array("classField"=>"generic", "js"=>"onclick=\"history.go(-1)\""));
		$buffer .= " ".$gform->input('save', 'button', _("salva template"), array("classField"=>"submit", "js"=>"onclick=\"saveTemplate();\""));
		$buffer .= $gform->close();

		$buffer .= "</div>\n";

		$buffer .= "</body>\n";
		$buffer .= "</html>\n";

		return $buffer;
	}
	
	private function createTemplate($blocks_number, $template='') {
	
		$buffer = '';
		$num = 1;
		for($i=1; $i<=$blocks_number; $i++) {
			
			$add_form = cleanVar($_POST, 'addblocks_'.$i, 'int', '');
			for($y=1; $y<=$add_form; $y++) {
				
				$ref_name = $i.'_'.$y;
				
				$width_add = cleanVar($_POST, 'width_add'.$ref_name, 'int', '');
				$um_add = cleanVar($_POST, 'um_add'.$ref_name, 'int', '');
				$align_add = cleanVar($_POST, 'align_add'.$ref_name, 'int', '');
				$rows_add = cleanVar($_POST, 'rows_add'.$ref_name, 'int', '');
				$cols_add = cleanVar($_POST, 'cols_add'.$ref_name, 'int', '');
				
				if($rows_add > 0 && $cols_add > 0)
				{
					$buffer .= $this->printBlock($num, $align_add, $rows_add, $cols_add, $um_add, $width_add);
					$num++;
				}
			}
			
			$delete = cleanVar($_POST, 'del'.$i, 'int', '');
			$align = cleanVar($_POST, 'align_'.$i, 'int', ''); 
			$rows = cleanVar($_POST, 'rows_'.$i, 'int', '');
			$cols = cleanVar($_POST, 'cols_'.$i, 'int', '');
			$um = cleanVar($_POST, 'um_'.$i, 'int', '');
			$width = cleanVar($_POST, 'width_'.$i, 'int', '');
			
			if($rows > 0 && $cols > 0 && $delete != 1)
			{
				$pos = $template ? $i : 0;
				$buffer .= $this->printBlock($num, $align, $rows, $cols, $um, $width, $pos, $template);
				$num++;
			}
		}
		
		return $buffer;
	}
	
	private function printBlock($num, $align, $rows, $cols, $um, $width, $pos=0, $template='') {
		
		if($align==2) $margin = "margin: auto;";
		elseif($align==3) $margin = "float: right;";
		else $margin = '';

		$um = $um == 1 ? 'px' : '%';
		$block_style_width = $width ? "width:".$width.$um.";" : '';

		if($um == 'px' && $width) $nav_style = "width:".floor($width/$cols)."px".($cols>1 ? ";float:left;" : "");
		else $nav_style = "width:".floor(100/$cols)."%".($cols>1 ? ";float:left;" : "");

		$old = false;
		if($pos && $template)
		{
			$db = db::instance();
			$query = "SELECT rows, cols FROM ".self::$_tbl_tpl_block." WHERE tpl='$this->id' AND position='$pos'";
			$a = $db->selectquery($query);
			if(sizeof($a)>0)
				$old = true;
		}
		
		$buffer = "<div id=\"block_$num\" style=\"$block_style_width$margin\">\n";

		for($ii=1; $ii<$rows+1; $ii++) {
			for($iii=1; $iii<$cols+1; $iii++) {
				
				$module = '';
				if($old)
				{
					$ref_nav = "nav_".$pos."_".$ii."_".$iii;
					$pattern = '#<div id="'.$ref_nav.'" style="([a-zA-Z0-9 ":;%=]+)">[\r\n ]*(\{[a-zA-Z0-9= \{\}\r\n]+\})?[\r\n ]*<\/div>#';
					if(preg_match($pattern, $template, $matches))
					{
						if($matches[0])
						{
							$nav_style = $matches[1];
							if(array_key_exists(2, $matches)) $module = $matches[2];
						}
					}
				}
				$buffer .= "<div id=\"nav_".$num."_".$ii."_".$iii."\" style=\"".$nav_style."\">";
				$buffer .= $module;
				$buffer .= "</div>";
			}
			$buffer .= "<div class=\"null\"></div>";
		}

		$buffer .= "</div>";
		$buffer .= "<div class=\"null\"></div>";
		
		return $buffer;
	}
	
	/**
	 * Blocco segnaposto nello schema del template
	 * 
	 * Si richiamano i metodi outputFunctions() delle classi dei moduli e dei moduli di sistema
	 * 
	 * @param array $matches
	 *   - $matches[0] complete matching 
	 *   - $matches[1] match open tag, es. <div id="nav_1_1" style="float:left;width:200px">
	 *   - $matches[3] match div id, es. nav_1_1
	 *   - $matches[4] match div content, es. {module classid=20 func=blockList}
	 *   - $matches[5] match close tag, es. </div>
	 * @return string
	 */
	private function renderNave($matches) {

    loader::import('page', 'PageEntry');
    loader::import('sysClass', 'ModuleApp');
    loader::import('module', 'ModuleInstance');
		
		$buffer = $matches[1];
		$buffer .= $this->cellCtrl($matches[3]);
		$buffer .= "<div id=\"sortables_".$matches[3]."\">";
		$count = 0;
		foreach(explode("\n", $matches[4]) as $mdlMarker) {
			if(preg_match("#module#", $mdlMarker)) {
				$mdlMarker = preg_replace("#[\r\n]#", "", $mdlMarker);
				preg_match("#\s(\w+)id=([0-9]+)\s*(\w+=(\w+))?#", $mdlMarker, $m);
				$mdlId = (!empty($m[2]))? $m[2]:null;
				$mdlType = (!empty($m[1]))? $m[1]:null;

        if($mdlType=='page') {
          $page = new PageEntry($mdlId);
					$title = $page->title;
          $jsurl = $page->getIdUrl(true);
				}
				elseif($mdlType=='class' || $mdlType=='class') {
          $module = new ModuleInstance($mdlId);
					$classname = $module->className();
					$title = $module->label;
					$mdlFunc = $m[4];
					$output_functions = (method_exists($classname, 'outputFunctions')) ? call_user_func(array($classname, 'outputFunctions')):array();
					$title .= " - ".$output_functions[$mdlFunc]['label'];
					$jsurl = $this->_home."?pt[".$module->name."-$mdlFunc]";
				}
				elseif($mdlType=='class' || $mdlType=='sysclass') {
          $module_app = new ModuleApp($mdlId);
					$classname = $module_app->className();
					$title = $module_app->label;
					$mdlFunc = $m[4];
					$output_functions = (method_exists($classname, 'outputFunctions'))? call_user_func(array($classname, 'outputFunctions')):array();
					$title .= " - ".$output_functions[$mdlFunc]['label'];
					$jsurl = $this->_home."?pt[$classname-$mdlFunc]";
				}
				elseif($mdlType=='' && $mdlId == 0) {
					$title = _("Modulo da url");
					$jsurl = null;
				}
				else exit(error::syserrorMessage("document", "renderModule", "Tipo di modulo sconosciuto", __LINE__));
 	
				$buffer .= "<div id=\"mdlContainer_".$matches[3]."_$count\">";
				$buffer .= "<div class=\"mdlContainerCtrl\">";
				$buffer .= "<div class=\"disposeMdl\"></div>";
				$buffer .= "<div class=\"sortMdl\"></div>";
				$buffer .= "<div class=\"toggleMdl\"></div>";
				$buffer .= "<div class=\"null\"></div>";
				$buffer .= "</div>";
				$buffer .= "<div id=\"refillable_".$matches[3]."_$count\" class=\"refillableFilled\">";
				$buffer .= "<input type=\"hidden\" name=\"navElement\" value=\"".$mdlMarker."\" />";
				$buffer .= "<div>".htmlChars($title)."</div>";
				$buffer .= "</div>";
				$buffer .= "<div id=\"fill_".$matches[3]."_$count\" style=\"display:none;\"></div>";
				$buffer .= "</div>";

				if($jsurl) {
					$buffer .= "<script>gino.ajaxRequest('post', '$jsurl', '', 'fill_".$matches[3]."_$count', {'script':true})</script>";
				}
				$count++;
			}
		}
		
		$buffer .= "<div id=\"mdlContainer_".$matches[3]."_$count\">";
		$buffer .= "<div class=\"mdlContainerCtrl\">";
		$buffer .= "<div class=\"disposeMdlDisabled\"></div>";
		$buffer .= "<div class=\"sortMdlDisabled\"></div>";
		$buffer .= "<div class=\"toggleMdlDisabled\"></div>";
		$buffer .= "<div class=\"null\"></div>";
		$buffer .= "</div>";
		$buffer .= "<div id=\"refillable_".$matches[3]."_$count\" class=\"refillable\">";
		$buffer .= "</div>";
		$buffer .= "<div id=\"fill_".$matches[3]."_$count\" style=\"display:none;\"></div>";
		$buffer .= "</div>";

		$buffer .= "</div>";
		$buffer .= "<div class=\"navSizeCtrl\"> &nbsp; <div class=\"widthCtrl\"></div></div>";
		$buffer .= $matches[5];
	
		return $buffer;
	}

	private function cellCtrl($id) {

		$buffer = "<div class=\"navCtrl\">";
		$buffer .= "<div class=\"left\">$id &#160;</div>";
		$buffer .= "<div class=\"left\"><span class=\"navWidth\"></span></div>";
		$buffer .= "<div class=\"right\">";
		$buffer .= "<div class=\"fineMoreWidthCtrl\" title=\""._("aumenta larghezza")."\"></div>";
		$buffer .= "<div class=\"fineLessWidthCtrl\" title=\""._("diminuisci larghezza")."\"></div>";
		$buffer .= "<div class=\"floatCtrl\" title=\""._("modifica proprietà float")."\"></div>";
		$buffer .= "<div class=\"disposeCtrl\" title=\""._("elimina navata")."\"></div>";
		$buffer .= "</div>";
		$buffer .= "<div class=\"null\"></div>";

		$buffer .= "</div>";	

		return $buffer;
	}

	/**
	 * Crea e modifica un template
	 */
	public function actionTemplate() {
	
		$tplContent = $_POST['tplform_text'];
		if(get_magic_quotes_gpc()) $tplContent = stripslashes($tplContent);	// magic_quotes_gpc = On

    $this->free = 0;
		$this->label = cleanVar($_POST, 'label', 'string', '');
		$this->description = cleanVar($_POST, 'description', 'string', '');
		$tplFilename = cleanVar($_POST, 'filename', 'string', '');
		if($tplFilename) $this->filename = $tplFilename.".tpl";
		$modTpl = cleanVar($_POST, 'modTpl', 'int', '');

		$action = ($this->id)? "modify":"insert";

		$link_error = $this->_home."?evt[$this->_interface-manageLayout]&block=template&action=$action";

		if(!$this->id && is_file(TPL_DIR.OS.$this->filename.".tpl")) 
			exit(error::errorMessage(array('error'=>_("Nome file già presente")), $link_error));
		
		if($fp = @fopen(TPL_DIR.OS.$this->filename, "wb")) {
			fwrite($fp, $tplContent) || exit(error::errorMessage(array('error'=>_("Impossibile scrivere il file")), $link_error));
			fclose($fp);
		}
		else exit(error::errorMessage(array('error'=>_("Impossibile creare il file"), 'hint'=>_("Controllare i permessi in scrittura all'interno della cartella ".TPL_DIR.OS)), $link_error));
		
		$this->updateDbData();

		//if(($this->id && $modTpl == 1) || !$this->id)
		if($this->id)
		{
			$blocks_number = cleanVar($_POST, 'blocks_number', 'int', '');
			$blocks_del = cleanVar($_POST, 'blocks_del', 'string', '');
			$blocks_del = json_decode(base64_decode($blocks_del));
			
			if(sizeof($blocks_del) > 0)
			{
				foreach($blocks_del AS $key=>$value)
				{
          $this->_db->delete(self::$_tbl_tpl_block, "id='$key'");
				}
			}
			
			for($i=1; $i<=$blocks_number; $i++) {
				
				$bid = cleanVar($_POST, 'id_'.$i, 'int', '');
				$width = cleanVar($_POST, 'width_'.$i, 'int', '');
				$um = cleanVar($_POST, 'um_'.$i, 'int', '');
				$align = cleanVar($_POST, 'align_'.$i, 'int', '');
				$rows = cleanVar($_POST, 'rows_'.$i, 'int', '');
				$cols = cleanVar($_POST, 'cols_'.$i, 'int', '');
				
				if($width == 0) $um = 0;
				if($rows > 0 && $cols > 0)
					$this->saveBlock($bid, $i, $width, $um, $align, $rows, $cols);
			}
		}
		
		header("Location: $this->_home?evt[$this->_interface-manageLayout]&block=template");
    exit();
	}

	private function saveBlock($id, $position, $width, $um, $align, $rows, $cols) {
		if($id)
		{
      $cnt = $this->_db->getNumRecords(self::$_tbl_tpl_block, "id='$id' AND position='$position'");
			if($cnt)
			{
        $res = $this->_db->update(array(
          'width' => $width,
          'um' => $um,
          'align' => $align,
          'rows' => $rows,
          'cols' => $cols
        ), self::$_tbl_tpl_block, "id='$id'");
        return $res;
			}
			else
			{
        $this->_db->delete(self::$_tbl_tpl_block, "id='$id'");
        $res = $this->_db->insert(array(
          'tpl' => $this->id,
          'position' => $position,
          'width' => $width,
          'um' => $um,
          'align' => $align,
          'rows' => $rows,
          'cols' => $cols
        ), self::$_tbl_tpl_block);
        return $res;
			}
		}
		else
		{
      $res = $this->_db->insert(array(
        'tpl' => $this->id,
        'position' => $position,
        'width' => $width,
        'um' => $um,
        'align' => $align,
        'rows' => $rows,
        'cols' => $cols
      ), self::$_tbl_tpl_block);
      return $res;
		}

	}

	private function deleteBlocks() {
    return $this->_db->delete(self::$_tbl_tpl_block, "tpl='".$this->id."'");
	}
	
	/**
	 * Duplica un template
	 */
	public function actionCopyTemplate() {
	
		$gform = Loader::load('Form', array('gform', 'post', false));
		$gform->save('dataform');
		$req_error = $gform->arequired();

		$ref = cleanVar($_POST, 'ref', 'int', '');
		$label = cleanVar($_POST, 'label', 'string', '');
		$filename = cleanVar($_POST, 'filename', 'string', '');
		$description = cleanVar($_POST, 'description', 'string', '');
		
		if($filename) $filename = $filename.'.tpl';
		
		$link_error = $this->_home."?evt[$this->_interface-manageLayout]&block=template&id=$ref&action=copy";
		
		if($req_error > 0) 
			exit(error::errorMessage(array('error'=>1), $link_error));
		
		// Valori del template da duplicare
		$obj = new Template($ref);
		
		if(is_file(TPL_DIR.OS.$filename)) 
			exit(error::errorMessage(array('error'=>_("Nome file già presente")), $link_error));
		else
		{
			if(!copy(TPL_DIR.OS.$obj->filename, TPL_DIR.OS.$filename))
				exit(error::errorMessage(array('error'=>_("Impossibile creare il file").' '.$filename, 'hint'=>_("Controllare i permessi in scrittura all'interno della cartella ".TPL_DIR.OS)), $link_error));
		}
		
		$db = db::instance();
    $db->insert(array(
      'filename' => $filename,
      'label' => $label,
      'description' => $description
    ), self::$_tbl_tpl);
		$id = $db->getlastid(self::$_tbl_tpl);
		
    $rows = $db->select('*', self::$_tbl_tpl_block, "tpl='$ref'");
		if($rows and count($rows))
		{
			foreach($rows AS $row)
			{
        $db->insert(array(
          'tpl' => $id,
          'position' => $row['position'],
          'width' => $row['width'],
          'um' => $row['um'],
          'align' => $row['align'],
          'rows' => $row['rows'],
          'cols' => $row['cols']
        ), self::$_tbl_tpl_block);
			}
		}

		header("Location: $this->_home?evt[$this->_interface-manageLayout]&block=template");
    exit();
	}
}
?>