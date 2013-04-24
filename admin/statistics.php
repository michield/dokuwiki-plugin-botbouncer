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
class admin_plugin_botbouncer_statistics extends DokuWiki_Admin_Plugin {

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
    
    function getMenuText($language) {
        return $this->getLang('menu_statistics');
    }

    /**
     * return sort order for position in admin menu
     */
    function getMenuSort() {
        return 150;
    }

    /**
     * handle user request
     */
    function handle() {
    }

    /**
     * output appropriate html
     */
    function html() {
        $this->_stats();
    }
    
    
    function _stats() {
        print $this->locale_xhtml('stats');

        $days = 7;
        $list = $this->_readlines($days);
        $all = $whitelisted = 0;
        $stats = array();
        foreach ($list as $line){
            if (!$line) continue;
            if (preg_match('/is whitelisted$/',$line)) {
              $stats['whitelisted'] += 1;
            } elseif (preg_match('/no match$/',$line)) {
              $stats['not spam'] += 1;
            } else {
              $data = explode("\t",$line);
              $stats[$data[1].' '.$data[2]] = (int) $stats[$data[1].' '.$data[2]] + 1;
              $all++;
            }
        }
        arsort($stats);

        printf('<p><b>'.$this->getLang('blocked').'</b></p>',$all,$days);

        echo '<table class="inline">';
        echo '<tr>';
        echo '<th>'.$this->getLang('percent').'</th>';
        echo '<th>'.$this->getLang('count').'</th>';
        echo '<th>'.$this->getLang('reason').'</th>';
        echo '</tr>';
        foreach ($stats as $code => $count){
            echo '<tr>';
            echo '<td>';
            printf("%.2f%%",100*$count/$all);
            echo '</td>';
            echo '<td>';
            echo $count;
            echo '</td>';
            echo '<td>';
            echo $code;
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }

    /**
     * Read loglines backward
     * 
     * taken from dokuwiki captcha plugin
     */
    function _readlines($days=7){
        global $conf;
        $file = $conf['cachedir'].'/botbouncer.log';

        $date  = time() - ($days*24*60*60);

        $data  = array();
        $lines = array();
        $chunk_size = 8192;

        if (!@file_exists($file)) return $data;
        $fp = fopen($file, 'rb');
        if ($fp===false) return $data;

        //seek to end
        fseek($fp, 0, SEEK_END);
        $pos = ftell($fp);
        $chunk = '';

        while($pos){

            // how much to read? Set pointer
            if ($pos > $chunk_size){
                $pos -= $chunk_size;
                $read = $chunk_size;
            } else {
                $read = $pos;
                $pos  = 0;
            }
            fseek($fp,$pos);

            $tmp = fread($fp,$read);
            if($tmp === false) break;
            $chunk = $tmp.$chunk;

            // now split the chunk
            $cparts = explode("\n",$chunk);

            // keep the first part in chunk (may be incomplete)
            if($pos) $chunk = array_shift($cparts);

            // no more parts available, read on
            if(!count($cparts)) continue;

            // put the new lines on the stack
            $lines = array_merge($cparts,$lines);

            // check date of first line:
            list($cdate) = explode("\t",$cparts[0]);
            if ($cdate < $date) break; // we have enough
        }
        fclose($fp);

        return $lines;
    }
}
//Setup VIM: ex: et ts=4 :
