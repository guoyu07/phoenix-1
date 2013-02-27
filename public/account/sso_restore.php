<?php

/**
 * Account restoration for *ANY* account type, based on SSO
 *
 * @author  Yectep Studios <info@yectep.hk>
 * @version 20707
 * @package Plume
 */


define('PTP',   '../../private/');
define('PHX_SCRIPT_TYPE',   'HTML');
define('PHX_UX',        true);


// Include common ignition class
require_once(PTP . 'php/ignition.php');

// Set page switch variables
$h['title'] = 'Restore Your Account';


// Include header section
echo UX::makeHead($h, $n, 'common/header_public', 'common/nav_public');

// Page info
//echo UX::makeBreadcrumb(array(  'Staff Portal'   => '/staff',   'Sign In'       => '/staff/index.php'));

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

echo UX::grabPage('common/masthead', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>