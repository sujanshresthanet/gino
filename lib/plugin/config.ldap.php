<?php
/**
 * @file config.ldap.php
 * @brief File di configurazione
 * 
 * Contiene i parametri ldap
 * 
 * @copyright 2013 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * Indirizzo del server
 * 
 * @var string (@example ldap://server.ldap.local)
 */
define('LDAP_HOST', '');

/**
 * Numero della porta di connessione (ldap 389, ldaps 636)
 * 
 * @var integer
 */
define('LDAP_PORT', '');

/**
 * Parametri di connessione al server
 * 
 * @var string
 */
define('LDAP_BASE_DN', '');

/**
 * Parametri di ricerca
 * 
 * @var string
 */
define('LDAP_SEARCH_DN', '');

/**
 * Username dell'applicazione
 * 
 * @var string
 */
define('LDAP_APP_USERNAME', '');

/**
 * Password dell'applicazione
 * 
 * @var string
 */
define('LDAP_APP_PASSWORD', '');

/**
 * Nome del dominio (per la costruzione degli indirizzi email, account+dominio)
 * 
 * @var string (es.: \@example.it)
 */
define('LDAP_DOMAIN', '');

/**
 * Numero della versione del protocollo
 * 
 * @var integer
 */
define('LDAP_PROTOCOL_VERSION', '');

?>