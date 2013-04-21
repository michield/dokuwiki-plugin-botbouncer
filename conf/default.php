<?php
/**
 * Options for the Bot Bouncer plugin
 *
 * @author Michiel Dethmers <hello@botbouncer.org>
 */

$conf['akismetapikey']  = '';
$conf['akismetblogurl']  = $_SERVER['HTTP_HOST'];
$conf['honeypotapikey']  = '';
$conf['mollomprivatekey']  = '';
$conf['mollompublickey']  = '';
$conf['usestopforumspam'] = 0;
$conf['continue'] = 0;
$conf['whitelist'] = $_SERVER['REMOTE_ADDR'];
$conf['spamerror'] = 'Sorry, there was an error processing your request. If this is an error contact us at info AT mydomain.com.' ;
