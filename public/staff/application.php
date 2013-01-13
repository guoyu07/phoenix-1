<?php

/**
 * Teacher's course application user page
 *
 * @author	Yectep Studios <info@yectep.hk>
 * @version	20707
 * @package Plume
 */


define('PTP',   '../../private/');
define('PHX_SCRIPT_TYPE',   'HTML');
define('PHX_NEWS',      true);
define('PHX_UX',        true);
define('PHX_MAILER',    true);
define('PHX_COURSES',   true);


// Include common ignition class
require_once(PTP . 'php/ignition.php');

// Get roomlist
$rooms = Courses::getRoomList();
$p['rooms'] = '';
foreach($rooms as $roomid => $rm) {
    $p['rooms'] .= '<option value="'.$roomid.'">'.$rm['name'].'</option>'."\n";
}

// Set page switch variables
$h['title'] = 'Course Application';
$n['application'] = 'active';

// Clean up page data
$p['error'] = $error;
$p['form_json'] = json_encode($_POST);


// Include header section
echo UX::makeHead($h, $n, 'common/header_staff', 'common/nav_public_staff');

// Page info
echo UX::makeBreadcrumb(array(	'Staff Portal'		=> '/staff/index.php', 'Course Application' => '/staff/application.php'));
echo UX::grabPage('staff/application', $p, true);

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

echo UX::grabPage('common/masthead_staff', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>