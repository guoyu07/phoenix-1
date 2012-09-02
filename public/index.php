<?php

/**
 * Default landing splash/info page.
 *
 * @author	Yectep Studios <info@yectep.hk>
 * @version	20813
 * @package Plume
 */


define('PTP',   '../private/');
define('PHX_SCRIPT_TYPE',   'HTML');
define('PHX_NEWS',      true);
define('PHX_UX',        true);


// Include common ignition class
require_once(PTP . 'php/ignition.php');

// Set page switch variables
$h['title'] = 'Welcome';
$b['body_class'] = 'billboarded';
$n['homepage'] = 'active';

// Include header section
echo UX::makeHead($h, $n);

// The page
echo UX::grabPage('public/home', $b, true);

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

echo UX::grabPage('common/masthead', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>