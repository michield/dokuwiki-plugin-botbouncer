<?php
/**
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michiel Dethmers <hello@botbouncer.org>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'action.php');

class action_plugin_botbouncer extends DokuWiki_Action_Plugin {

    /**
     * return some info
     */
    function getInfo(){
        return confToHash(dirname(__FILE__).'/plugin.info.txt');
    }

    /**
     * register the eventhandlers and initialize some options
     */
    function register(&$controller){
        $controller->register_hook('DOKUWIKI_STARTED',
                                   'BEFORE',
                                   $this,
                                   'handle_start',
                                   array());
    }

    function handle_start(&$event, $param) {
   
      if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      
       // error_reporting(E_ALL);
       // ini_set('display_errors',true);
        $honeypotApiKey = $this->getConf('honeypotapikey');
        $akismetApiKey = $this->getConf('akismetapikey');
        $akismetUrl = $this->getConf('akismetblogurl');
        $mollomPublicKey = $this->getConf('mollompublickey');
        $mollomPrivateKey = $this->getConf('mollomprivatekey');
        $continue = $this->getConf('continue');
        $spamError = $this->getConf('spamerror');
        include dirname(__FILE__).'/lib/botbouncer.php';
        $fsc = new botBouncer($honeypotApiKey,$akismetApiKey,$akismetUrl,$mollomPrivateKey,$mollomPublicKey);
        $fsc->setLogRoot($GLOBALS['conf']['cachedir']);
        if ($fsc->isSpam(
          array(
            'username' => $_SESSION[DOKU_COOKIE]['auth']['info']['name'],
            'email' => $_SESSION[DOKU_COOKIE]['auth']['info']['mail'],
            'ips' => array($_SERVER['REMOTE_ADDR']),
          ),
          !empty($continue)
        )) {
          print $spamError;exit;
        //} else {
          //print "This is ham";
        }
      }
    }

}


