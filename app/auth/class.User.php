<?php
/**
 * @file class.User.php
 * Contiene la definizione ed implementazione della classe Gino.App.Auth.User.
 * 
 * @copyright 2013-2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
namespace Gino\App\Auth;

/**
 * @brief Classe di tipo Gino.Model che rappresenta un utente
 *
 * @copyright 2013-2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class User extends \Gino\Model {

    public static $table = TBL_USER;
    public static $table_groups = TBL_USER_GROUP;
    public static $table_more = TBL_USER_ADD;
    public static $columns;

    private static $extension_media;
    
    private static $lng_nav;
    private static $lng_dft;

    /**
     * @brief Costruttore
     * 
     * @param integer $id valore ID del record
     * @return istanza di Gino.App.Auth.User
     */
    function __construct($id) {

        $this->_tbl_data = self::$table;

        $this->_controller = new auth();

        $registry = \Gino\Registry::instance();
        self::$extension_media = \Gino\enabledPng() ? array('jpg') : array('png', 'jpg');
        
        self::$lng_nav = $this->_lng_nav;
        self::$lng_dft = $this->_lng_dft;

        parent::__construct($id);
    }

    /**
     * @brief Rappresentazione a stringa dell'oggetto
     * @return nome e cognome
     */
    function __toString() {
        return (string) ($this->lastname.' '.$this->firstname);
    }

	public static function setProperties($model) {
		
		$base_path = $this->_controller->getBasePath();
		$add_path = $this->_controller->getAddPath($this->id);
		
		$property['photo'] = array(
			'model'=>$model,
			'extensions'=>self::$extension_media,
			'path'=>$base_path,
			'add_path'=>$add_path
		);
		$property['email'] = array(
			'model'=>$model,
			'trnsl'=>false,
		);
		
		return $property;
	}
	
    /**
      * Struttura dei campi della tabella di un modello
      *
      * @return array
      */
     public static function columns() {
     
     	$columns['id'] = new \Gino\IntegerField(array(
     			'name'=>'id',
     			'primary_key'=>true,
     			'auto_increment'=>true,
     	));
     	$columns['firstname'] = new \Gino\CharField(array(
     			'name'=>'firstname',
     			'label' => _("Nome"),
     			'required'=>true,
     			'max_lenght'=>50,
     	));
     	$columns['lastname'] = new \Gino\CharField(array(
     			'name'=>'lastname',
     			'label' => _("Cognome"),
     			'required'=>true,
     			'max_lenght'=>50,
     	));
     	$columns['company'] = new \Gino\CharField(array(
     			'name'=>'company',
     			'label' => _("Società"),
     			'required'=>false,
     			'max_lenght'=>100,
     	));
     	$columns['phone'] = new \Gino\CharField(array(
     			'name'=>'phone',
     			'label' => _("Telefono"),
     			'required'=>false,
     			'max_lenght'=>30,
     	));
     	$columns['fax'] = new \Gino\CharField(array(
     			'name'=>'company',
     			'required'=>false,
     			'max_lenght'=>30,
     	));
     	$columns['email'] = new \Gino\EmailField(array(
     			'name'=>'email',
     			'label'=>_("Email"),
     			'required'=>true,
     			//'trnsl'=>false,
     			'max_lenght'=>100,
     	));
     	$columns['username'] = new \Gino\CharField(array(
     			'name'=>'username',
     			'label' => _("Username"),
     			'required'=>true,
     			'max_lenght'=>50,
     	));
     	$columns['userpwd'] = new \Gino\CharField(array(
     			'name'=>'userpwd',
     			'label' => _("Password"),
     			'required'=>true,
     			'max_lenght'=>100,
     	));
     	$columns['is_admin'] = new \Gino\BooleanField(array(
     			'name'=>'is_admin',
     			'label' => _('Super-amministratore'),
     			'required'=>true,
     			'enum'=>array(1 => _('si'), 0 => _('no')),
     			'default'=>0,
     	));
     	$columns['address'] = new \Gino\CharField(array(
     			'name'=>'address',
     			'label' => _("Indirizzo"),
     			'required'=>false,
     			'max_lenght'=>200,
     	));
     	$columns['cap'] = new \Gino\IntegerField(array(
     			'name'=>'cap',
     			'label' => _("CAP"),
     			'required'=>false
     	));
     	$columns['city'] = new \Gino\CharField(array(
     			'name'=>'city',
     			'label' => _("Città"),
     			'required'=>false,
     			'max_lenght'=>50,
     	));
     	
     	$db = \Gino\Db::instance();
     	$nations = array();
     	$lang_nation = self::getLanguageNation();
     	$rows = $db->select('id, '.$lang_nation, TBL_NATION, null, array('order' => $lang_nation.' ASC'));
     	foreach($rows as $row) {
     		$nations[$row['id']] = \Gino\htmlChars($row[$lang_nation]);
     	}
     	
     	$columns['nation'] = new \Gino\EnumField(array(
     			'name'=>'nation',
     			'label'=>_("Nazione"),
     			'widget'=>'select',
     			'enum'=>$nations,
     	));
     	$columns['text'] = new \Gino\TextField(array(
     			'name'=>'text',
     			'label' => _("Informazioni"),
     			'required'=>false
     	));
     	
     	//$base_path = $controller->getBasePath();
     	//$add_path = $this->_controller->getAddPath($this->id);
     	
     	$columns['photo'] = new \Gino\ImageField(array(
     			'name'=>'photo',
     			'label'=>_("Foto"),
     			'required'=>false,
     			//'extensions'=>self::$extension_media,
     			//'path'=>array($ns_controller, 'getBasePath'),
     			//'path'=>$base_path,
     			//'add_path'=>$add_path,
     			'max_lenght'=>50,
     	));
     	$columns['publication'] = new \Gino\BooleanField(array(
     			'name'=>'publication',
     			'label'=>_('Pubblicazione dati'),
     			'required'=>false,
     			'enum'=>array(1=>_('si'), 0=>_('no')),
     			'default'=>0,
     	));
     	$columns['date'] = new \Gino\DatetimeField(array(
     			'name'=>'date',
     			'required'=>true
     	));
     	$columns['active'] = new \Gino\BooleanField(array(
     			'name'=>'active',
     			'label'=>_('Attivo'),
     			'required'=>true,
     			'enum'=>array(1=>_('si'), 0=>_('no')),
     			'default'=>0,
     	));
     	$columns['ldap'] = new \Gino\BooleanField(array(
     			'name'=>'ldap',
     			'label'=>_('Ldap'),
     			'required'=>true,
     			'enum'=>array(1=>_('si'), 0=>_('no')),
     			'default'=>0,
     	));
     	
     	$registry = \Gino\Registry::instance();
     	
     	$columns['groups'] = new \Gino\ManyToManyField(array(
     			'name'=>'groups',
     			'label'=>_("Gruppi"),
     			'required'=>false,
     			'm2m'=>'\Gino\App\Auth\Group',
     			'm2m_where'=>null,
     			'm2m_order'=>'name ASC',
     			'join_table'=>self::$table_groups,
     			'self'=>'\Gino\App\Auth\User',
     			'add_related' => true,
     			'add_related_url' => $registry->router->link('auth', 'manageAuth', array(), "block=group&insert=1")
     	));
     	
     	return $columns;
     }
     
     /**
      * Codice lingua per l'elenco nazioni nella tabella nation
      * 
      * @return string
      */
     private static function getLanguageNation() {
     	
     	$lang = array('it_IT', 'en_US', 'fr_FR');
     	$lang_default = $lang[1];
     	
     	if(!in_array(self::$lng_nav, $lang))
     	{
     		$lang_nation = in_array(self::$lng_dft, $lang) ? self::$lng_dft : $lang_default;
     	}
     	else
     	{
     		$lang_nation = $lang_default;
     	}
     	return $lang_nation;
     }

     /**
      * @brief Form per cambiare la password
      * 
      * @param array $options
      *   array associativo di opzioni
      *   - @b form_action (string): indirizzo del form action
      *   - @b rules (string): descrizione delle regole alle quali è sottoposta la password
      *   - @b maxlength (integer): numero massimo di caratteri
      * @return html, form
      */
     public function formPassword($options=array()) {

         $form_action = \Gino\gOpt('form_action', $options, null);
         $rules = \Gino\gOpt('rules', $options, null);
         $maxlength = \Gino\gOpt('maxlength', $options, null);

         $gform = \Gino\Loader::load('Form', array('pwdform', 'post', true));

         $gform->load('pwdform');

         $required = 'userpwd,check_userpwd';

         $buffer = $gform->open($form_action, '', $required);
         $buffer .= $gform->hidden('id', $this->id);

         $buffer .= $gform->cinput('userpwd', 'password', '', array(_("Password"), $rules), array("required"=>true, "size"=>40, "maxlength"=>$maxlength));
         $buffer .= $gform->cinput('check_userpwd', 'password', '', _("Verifica password"), array("required"=>true, "size"=>40, "maxlength"=>$maxlength, "other"=>"autocomplete=\"off\""));

         $buffer .= $gform->cinput('submit_action', 'submit', _("procedi"), '', array("classField"=>"submit"));

         $buffer .= $gform->close();

         return $buffer;
    }

    /**
     * @brief Processa il form cambio password
     * 
     * @see self::formPassword()
     * @param array $options
     *   array associativo di opzioni (per il metodo checkPassword())
     *   - @b pwd_length_min (integer): numero minimo di caratteri della password
     *   - @b pwd_length_max (integer): numero massimo di caratteri della password
     *   - @b pwd_numeric_number (integer): numero di caratteri numerici da inserire nella password
     * @return boolean
     */
    public function savePassword($options=array()) {

        $request = \Gino\Http\Request::instance();
        $gform = \Gino\Loader::load('Form', array('pwdform', 'post', true));

        $gform->save('pwdform');
        $req_error = $gform->arequired();

        if($req_error > 0) 
            return array('error'=>1);

        $password = \Gino\cleanVar($request->POST, 'userpwd', 'string', '');
         $options['password'] = $password;

        $check_password = self::checkPassword($options);

        if(is_array($check_password))
            return $check_password;

        $password = self::setPassword($password);

        $result = $this->_db->update(array('userpwd'=>$password), self::$table, "id='".$this->id."'");

        return $result;
     }

     /**
      * @brief Imposta la password (dal form di inserimento utente)
      * 
      * @see generatePassword()
      * @see pub::cryptMethod()
      * @param string $password
      * @param array $options
      *   array associativo di opzioni
      *   - @b aut_password (boolean): la password è generata dal sistema (default false)
      * @return string (password) oppure array (error)
      */
     public static function setPassword($password, $options=array()) {

        $aut_password = \Gino\gOpt('aut_password', $options, false);

        if(is_null($password) && $aut_password)
        {
            $password = self::generatePassword($options);
        }
        else
        {
            $registry = \Gino\registry::instance();
            $crypt_method = $registry->sysconf->password_crypt;

            $password = $crypt_method ? \Gino\cryptMethod($password, $crypt_method) : $password;
        }
        return $password;
     }

    /**
     * @brief Verifica la conformità di una password
     * 
     * Parametri POST: \n
     *   - userpwd (string)
     *   - check_userpwd (string)
     * 
     * Se è presente l'input form @a check_userpwd viene controllata la corrispondenza con l'input form @a userpwd.
     * @param array $options
     *   array associativo di opzioni
     *   - @b password (string): valore della password (se non indicato viene recuperato il valore dal POST)
     *   - @b check_password (string): valore di controllo della password (se non indicato viene recuperato il valore dal POST)
     *   - @b pwd_length_min (integer): numero minimo di caratteri della password
     *   - @b pwd_length_max (integer): numero massimo di caratteri della password
     *   - @b pwd_numeric_number (integer): numero di caratteri numerici da inserire nella password
     * @return array (errore) oppure TRUE
     */
    public static function checkPassword($options=array()){

        $request = \Gino\Http\Request::instance();
        $password = \Gino\gOpt('password', $options, null);
        $password_check = \Gino\gOpt('password_check', $options, null);
        $pwd_length_min = \Gino\gOpt('pwd_length_min', $options, null);
        $pwd_length_max = \Gino\gOpt('pwd_length_max', $options, null);
        $pwd_numeric_number = \Gino\gOpt('pwd_numeric_number', $options, null);

        if(is_null($password)) $password = \Gino\cleanVar($request->POST, 'userpwd', 'string', '');
        if(is_null($password_check)) $password_check = \Gino\cleanVar($request->POST, 'check_userpwd', 'string', '');

        if($password_check && $password != $password_check)
            return array('error'=>6);

        $regex = '';
        $base = "[0-9]{1}.*";
        if($pwd_numeric_number > 0)
        {
            $regex .= '/^.*';

            for($i=0; $i<$pwd_numeric_number; $i++)
            {
                $regex .= $base;
            }
            $regex .= '$/';

            $check = true;
        }
        else $check = false;

        if($check)
        {
            if((strlen($password) < $pwd_length_min OR strlen($password) > $pwd_length_max) || !preg_match($regex, $password))
                return array('error'=>19);
        }
        else
        {
            if(strlen($password) < $pwd_length_min OR strlen($password) > $pwd_length_max)
                return array('error'=>19);
        }

        return true;
    }

    /**
     * @brief Genera una password (random)
     * 
     * @param array $options
     *   array associativo di opzioni
     *   - @b aut_password_length (integer): numero di caratteri della password automatica
     * @return password
     */
    public static function generatePassword($options=array()){

        $password_length = \Gino\gOpt('aut_password_length', $options, null);

        if(!$password_length) return null;

        //set alphabet arrays
        $char_alpha_lower = array("a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z");
        $char_alpha_upper = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");
        $char_numeric  = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '0');
        $char_special = array('_', '!', '?', '@');
        // end

        $alpha_pwd = array_merge($char_alpha_lower, $char_alpha_upper);
        $other_pwd = array_merge($char_numeric, $char_special);
        $name_pwd = array('alpha_pwd', 'other_pwd');

        $password = '';

        for($i = 0; $i < $password_length; $i++)
        {
            // scelta random tra $alpha_pwd e $other_pwd (50%)
            $key_choice = mt_rand(0, sizeof($name_pwd)-1);
            $choice = $name_pwd[$key_choice];
            $choice_array = $$choice;

            $key_rand = mt_rand(0, sizeof($choice_array)-1);
            $char = $choice_array[$key_rand];

            $password .= $char;
        }

        return $password;
    }

    /**
     * @brief Verifica la conformità e validità di un indirizzo email
     * 
     * Parametri POST: \n
     *   - email (string)
     *   - check_email (string)
     * 
     * @see Gino.checkEmail()
     * @param integer $id valore ID del record sul quale non si effettua il controllo (per le operazioni di modifica)
     * @return array (error) or true
     */
    public static function checkEmail($id=null){

        $request = \Gino\Http\Request::instance();
        $db = \Gino\db::instance();

        $email = \Gino\cleanVar($request->POST, 'email', 'string', '');
        $check_email = \Gino\cleanVar($request->POST, 'check_email', 'string', '');

        if($email && !\Gino\checkEmail($email, true))
            return array('error'=>7);

        if($db->columnHasValue(self::$table, 'email', $email, array('except_id'=>$id)))
            return array('error'=>20);

        if($check_email && $email != $check_email)
            return array('error'=>25);

        return true;
    }

    /**
     * @brief Verifica l'unicità dello username
     * 
     * Parametri POST: \n
     *   - username (string)
     *   - email (string)
     * 
     * @param array $options
     *   array associativo di opzioni
     *   - @b username_as_email (boolean): indica se come username viene utilizzata l'email (default false)
     * @return array (error) or true
     */
    public static function checkUsername($options) {

        $request = \Gino\Http\Request::instance();
        $username_as_email = \Gino\gOpt('username_as_email', $options, false);

        $db = \Gino\db::instance();

        $input_name = $username_as_email ? 'email' : 'username';

        $username = \Gino\cleanVar($request->POST, $input_name, 'string', '');

        if($db->columnHasValue(self::$table, 'username', $username))
            return array('error'=>8);

        return true;
    }

    /**
     * @brief Restituisce l'utente dati i valori username e password
     * 
     * @see Gino.Access::Authentication()
     * @param string $username lo username
     * @param string $password la password
     * @param boolean $auth_ldap risultato dell'autenticazione ldap
     * @return mixed Gino.App.Auth.User ricavato oppure null
     */
    public static function getFromUserPwd($username, $password, $auth_ldap) {

        $db = \Gino\Db::instance();

        $user = null;

        $registry = \Gino\Registry::instance();
        $crypt_method = $registry->sysconf->password_crypt;

        $password = $crypt_method ? \Gino\cryptMethod($password, $crypt_method) : $pwd;

        $rows = $db->select('id, ldap', self::$table, "username='$username' AND userpwd='$password' AND active='1'");
        if($rows and count($rows) == 1) {
            
        	$user_id = $rows[0]['id'];
        	$user_ldap = $rows[0]['ldap'];
        	
        	if(($auth_ldap && $user_ldap) || !$auth_ldap) {
        		$user = new User($user_id);
        	}
        }
        return $user;
    }

    /**
     * @brief Restituisce l'utente legato all'email data
     *
     * @param string $email
     * @param bool $active considera solo utenti attivi, default TRUE
     * @return Gino.App.Auth.User oggetto utente
     */
    public static function getFromEmail($email, $active=TRUE)
    {
        $users = User::objects(null, array('where' => "email='".$email."'".($active ? " AND active='1'" : "")));
        if($users and count($users)) {
            return $users[0];
        }
        return null;
    }

    /**
     * @brief Elenco degli utenti associati a uno o più permessi
     * 
     * @param string!array $code codice o codici del permesso
     * @param object $controller controller
     * @return array (valori id)
     */
    public static function getUsersFromPermissions($code, $controller) {

        $db = \Gino\db::instance();

        $array = array();

        $class = get_class($controller);
        $instance = $controller->getInstance();

        if(is_array($code) && count($code))
        {
            foreach ($code AS $value)
            {
                $res = $db->select('id', Permission::$table, "class='$class' AND code='$value'");
                if($res && count($res))
                {
                    $perm_id = $res[0];
                    $records = $db->select('user_id', Permission::$table_perm_user, "instance='$instance' AND perm_id='$perm_id'");
                    if($records && count($records))
                    {
                        foreach ($records AS $r)
                        {
                            if(!in_array($r['user_id'], $array))
                                $array[] = $r['user_id'];
                        }
                    }
                }
            }
        }
        elseif(is_string($code))
        {
            $res = $db->select('id', Permission::$table, "class='$class' AND code='$value'");
            if($res && count($res))
            {
                $perm_id = $res[0];
                $records = $db->select('user_id', Permission::$table_perm_user, "instance='$instance' AND perm_id='$perm_id'");
                if($records && count($records))
                {
                    foreach ($records AS $r)
                    {
                        if(!in_array($r['user_id'], $array))
                            $array[] = $r['user_id'];
                    }
                }
            }
        }

        return $array;
    }

    /**
     * @brief Verifica se l'utente ha uno dei permessi della classe
     * @param string $class_name nome della classe
     * @param int|array $perms id o array di id dei permessi da verificare
     * @param int $instance istanza della classe (0 per classi non istanziabili)
     * @return bool
     */
    public function hasPerm($class_name, $perms, $instance = 0) {
        if(!$this->id) {
            return false;
        }
        elseif($this->is_admin) {
            return true;
        }

        if(!is_array($perms)) {
            $perms = array($perms);
        }
        foreach($perms as $perm_code) {
            // check user permission
            $rows = $this->_db->select(TBL_USER_PERMISSION.'.perm_id', array(TBL_USER_PERMISSION, TBL_PERMISSION), "
                ".TBL_USER_PERMISSION.".perm_id = ".TBL_PERMISSION.".id AND 
                ".TBL_USER_PERMISSION.".user_id = '".$this->id."' AND 
                ".TBL_USER_PERMISSION.".instance = '".$instance."' AND 
                ".TBL_PERMISSION.".class = '".$class_name."' AND 
                ".TBL_PERMISSION.".code = '$perm_code'");
            if($rows and count($rows)) {
                return true;
            }
            // check user group permission
            $rows = $this->_db->select(TBL_GROUP_PERMISSION.'.perm_id', array(TBL_GROUP_PERMISSION, TBL_USER_GROUP, TBL_PERMISSION), "
                ".TBL_GROUP_PERMISSION.".perm_id = ".TBL_PERMISSION.".id AND 
                ".TBL_PERMISSION.".class = '".$class_name."' AND 
                ".TBL_PERMISSION.".code = '".$perm_code."' AND 
                ".TBL_GROUP_PERMISSION.".instance = '".$instance."' AND 
                ".TBL_GROUP_PERMISSION.".group_id = ".TBL_USER_GROUP.".group_id AND
                ".TBL_USER_GROUP.".user_id = '".$this->id."'");
            if($rows and count($rows)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @brief Verifica se l'utente ha uno dei permessi amministrativi della classe
     * @param string $class_name il nome della classe
     * @param int $instance istanza della classe (0 per classi non istanziabili)
     * @return bool
     */
    public function hasAdminPerm($class_name, $instance = 0) {

        $perms = array();
        $rows = $this->_db->select('code', TBL_PERMISSION, "admin='1'");
        if($rows and count($rows)) {
            foreach($rows as $row) {
                $perms[] = $row['code'];
            }
        }
        if($this->hasPerm($class_name, $perms, $instance)) {
            return true;
        }

        return false;
    }

    /**
     * @brief Valore che raggruppa permesso e istanza
     * 
     * @param integer $permission_id valore ID del permesso
     * @param integer $instance_id valore ID dell'istanza
     * @return string
     */
    public static function setMergeValue($permission_id, $instance_id) {

        return $permission_id.'_'.$instance_id;
    }

    /**
     * @brief Splitta i valori di permesso e istanza
     * 
     * @param string $value valore da splittare
     * @return array array(permission_id, instance_id)
     */
    public static function getMergeValue($value) {

        return explode('_', $value);
    }

    /**
     * @brief Elenco dei permessi di un utente
     * 
     * @see setMergeValue()
     * @param integer $id valore ID dell'utente
     * @return array permessi
     */
    public function getPermissions() {

        $items = array();

        $records = $this->_db->select('instance, perm_id', Permission::$table_perm_user, "user_id='".$this->id."'");
        if($records && count($records))
        {
            foreach($records AS $r)
            {
                $items[] = self::setMergeValue($r['perm_id'], $r['instance']);
            }
        }
        return $items;
    }

    /**
     * @brief Gestisce i record della tabella aggiuntiva degli utenti
     * 
     * @param integer $id valore ID dell'utente
     * @return boolean
     */
    public static function setMoreInfo($id) {

        $request = \Gino\Http\Request::instance();
        $db = \Gino\db::instance();

        $field1 = \Gino\cleanVar($request->POST, 'field1', 'int', '');
        $field2 = \Gino\cleanVar($request->POST, 'field2', 'int', '');
        $field3 = \Gino\cleanVar($request->POST, 'field3', 'int', '');

        $res = false;

        if($db->getFieldFromId(self::$table_more, 'user_id', 'user_id', $id))
        {
            $res = $db->update(array('field1'=>$field1, 'field2'=>$field2, 'field3'=>$field3), self::$table_more, "user_id='$id'");
        }
        else
        {
            $res = $db->insert(array('user_id'=>$id, 'field1'=>$field1, 'field2'=>$field2, 'field3'=>$field3), self::$table_more);
        }
        return $res;
    }

    /**
     * @brief Eimina i record della tabella aggiuntiva degli utenti
     * 
     * @param integer $id valore ID dell'utente
     * @return boolean
     */
    public static function deleteMoreInfo($id) {

        $db = \Gino\Db::instance();

        if($db->getFieldFromId(self::$table_more, 'user_id', 'user_id', $id))
        {
            return $db->delete(self::$table_more, "user_id='$id'");
        }
        else return TRUE;
    }

    /**
     * @brief Eliminazione utente
     * @see Model::delete()
     */
    public function delete() {

        $pathToDel = $this->_controller->getBasePath();

        $parent = parent::delete();
        if($parent !== TRUE) return $parent;

        return self::deleteMoreInfo($this->id);

        /*
        // Nel caso di una directory per ogni utente

        $pathToDel = $this->_controller->getBasePath().$this->_controller->getAddPath($this->id)

        if($pathToDel)
        {
            $registry = registry::instance();
            $registry->pub->deleteFileDir($pathToDel);
        }*/
    }

}

User::$columns=User::columns();
