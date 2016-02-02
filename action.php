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
    function register(Doku_Event_Handler $controller){
        $controller->register_hook('DOKUWIKI_STARTED',
                                   'BEFORE',
                                   $this,
                                   'handle_start',
                                   array());
    }

    function handle_start(&$event, $param) {
        ## handle whitelist
        $whitelist_ips = explode(',',$this->getConf('whitelist'));
        $whitelist_ips = array_map('trim', $whitelist_ips);
        if (in_array($_SERVER['REMOTE_ADDR'],$whitelist_ips)) {
        return;
        }
        $ips = array();
        if (isset($_SERVER['REMOTE_ADDR'])) {
          $ips[] = $_SERVER['REMOTE_ADDR'];
          if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips[] = $_SERVER['HTTP_X_FORWARDED_FOR'];
          }
        }
      
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $isRegister = 0;
            if ($_REQUEST['do'] == 'register' && $_POST['save'] == 1) {
                $email = $_POST['email'];
                $username = $_POST['login'];
                $content = $_POST['fullname'];
                $isRegister = 1;
            } else {
                $username = $_SESSION[DOKU_COOKIE]['auth']['info']['name'];
                $email = $_SESSION[DOKU_COOKIE]['auth']['info']['mail'];
                $content = ''; ##@@TODO find some field to pass on here
            }
      
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
        #         'test' => 'spam',
        #         'test' => 'ham',
                'username' => $username,
                'email' => $email,
                'content' => $content,
                'ips' => $ips,
              ),
              !empty($continue)
            )) {
          
            $logLine = time()."\t".$fsc->matchedBy. "\t".$fsc->matchedOn;
            unset($_POST['save']);
            if (!$isRegister) {
              ## @@TODO return a "nice error" ie in the page
              ## whilst blocking any further action
                print $spamError;exit;
            }
        } else {
            $logLine = time().' no match';
            //print "This is ham";
        }
        file_put_contents($GLOBALS['conf']['cachedir'].'/botbouncer.log',$logLine."\n",FILE_APPEND);
      }
    }

}


