<?php
/**
 * @file class.HttpResponseServerError.php
 * @brief Contiene la definizione ed implementazione della classe HttpResponseServerError
 *
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino;

/**
 * @brief Subclass di \Gino\HttpResponse per gestire risposte a seguito di errori interni (code 500)
 *
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class HttpResponseServerError extends HttpResponse {

    /**
     * @brief Costruttore
     * @param array $kwargs
     * @return istanza di \Gino\HttpResponseServerError
     */
    function __construct(array $kwargs = array()) {

        parent::__construct('', $kwargs);

        $this->setStatus(500, 'Internal Server Error');

    }

    /**
     * @brief Corpo della risposta HTTP
     * @description Mostra la pagina 500 di gino
     * @return void
     */
    protected function sendContent() {

        $document = Loader::load('Document', array(\Gino\App\Sysfunc\sysfunc::page500()));
        $buffer = $document->render();

        ob_start();
        echo $buffer;
        ob_end_flush();
    }

}
