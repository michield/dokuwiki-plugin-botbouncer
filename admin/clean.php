<?php
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'admin.php');
        //error_reporting(E_ALL);
       //ini_set('display_errors',true);

/**
 * All DokuWiki plugins to extend the admin function
 * need to inherit from this class
 */
class admin_plugin_botbouncer_clean extends DokuWiki_Admin_Plugin {
    var $_auth = null;        // auth object
    var $_user_total = 0;     // number of registered users
    var $_filter = array();   // user selection filter(s)
    var $_start = 0;          // index of first user to be displayed
    var $_last = 0;           // index of the last user to be displayed
    var $_pagesize = 50;      // number of users to list on one page
    var $_edit_user = '';     // set to user selected for editing
    var $_edit_userdata = array();
    var $_disabled = '';      // if disabled set to explanatory string

    function __construct() {
        global $auth;

        $this->setupLocale();

        if (!isset($auth)) {
          $this->disabled = $this->lang['noauth'];
        } else if (!$auth->canDo('getUsers')) {
          $this->disabled = $this->lang['nosupport'];
        } else {

          // we're good to go
          $this->_auth = & $auth;

        }
    }


    /**
     * return some info
     */
    function getInfo() {
        return confToHash(dirname(__FILE__).'/../plugin.info.txt');
    }
    
    /**
     * Access for managers allowed
     */
    function forAdminOnly() {
        return false;
    }

    /**
     * return sort order for position in admin menu
     */
    function getMenuSort() {
        return 150;
    }

    function getMenuText($language) {
        return $this->getLang('menu_clean');
    }

    /**
     * handle user request
     * 
     * some stuff taken from the usermanager plugin
     * @@TODO, finish
     * 
     */
    function handle() {
//        error_reporting(E_ALL);
//        ini_set('display_errors','on');
        global $INPUT;
        if (is_null($this->_auth)) return false;

        // extract the command and any specific parameters
        // submit button name is of the form - fn[cmd][param(s)]
        $fn   = $INPUT->param('fn');

        if (is_array($fn)) {
            $cmd = key($fn);
            $param = is_array($fn[$cmd]) ? key($fn[$cmd]) : null;
        } else {
            $cmd = $fn;
            $param = null;
        }

        if ($cmd != "search") {
          $this->_start = $INPUT->int('start', 0);
          $this->_filter = $this->_retrieveFilter();
        }

        switch($cmd){
          case "add"    : $this->_addUser(); break;
          case "delete" : $this->_deleteUser(); break;
          case "modify" : $this->_modifyUser(); break;
          case "edit"   : $this->_editUser($param); break;
          case "search" : $this->_setFilter($param);
                          $this->_start = 0;
                          break;
        }

        $this->_user_total = $this->_auth->canDo('getUserCount') ? $this->_auth->getUserCount($this->_filter) : -1;

        // page handling
        switch($cmd){
          case 'start' : $this->_start = 0; break;
          case 'prev'  : $this->_start -= $this->_pagesize; break;
          case 'next'  : $this->_start += $this->_pagesize; break;
          case 'last'  : $this->_start = $this->_user_total; break;
        }
        $this->_validatePagination();
    }

    /**
     * output appropriate html
     */
    function html() {
        print $this->locale_xhtml('clean');
        
        $user_list = $this->_auth->retrieveUsers($this->_start, $this->_pagesize, $this->_filter);
        $users = array_keys($user_list);

   #     var_dump($user_list);
    }

    function _validatePagination() {

        if ($this->_start >= $this->_user_total) {
          $this->_start = $this->_user_total - $this->_pagesize;
        }
        if ($this->_start < 0) $this->_start = 0;

        $this->_last = min($this->_user_total, $this->_start + $this->_pagesize);
    }
    
    function _retrieveFilter() {
        global $INPUT;

        $t_filter = $INPUT->arr('filter');

        // messy, but this way we ensure we aren't getting any additional crap from malicious users
        $filter = array();

        if (isset($t_filter['user'])) $filter['user'] = $t_filter['user'];
        if (isset($t_filter['name'])) $filter['name'] = $t_filter['name'];
        if (isset($t_filter['mail'])) $filter['mail'] = $t_filter['mail'];
        if (isset($t_filter['grps'])) $filter['grps'] = $t_filter['grps'];

        return $filter;
    }

}
//Setup VIM: ex: et ts=4 :
