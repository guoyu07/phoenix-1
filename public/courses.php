<?php

/**
 * CSS style definition guide as POC
 *
 * @author	Yectep Studios <info@yectep.hk>
 * @version	20825
 * @package Plume
 */


define('PTP',   '../private/');
define('PHX_SCRIPT_TYPE',   'HTML');
define('PHX_NEWS',      true);
define('PHX_UX',        true);
define('PHX_COURSES',   true);

// Include common ignition class
require_once(PTP . 'php/ignition.php');

// Set page switch variables
$h['title'] = 'Course Listing and Search';
$n['courses'] = 'active';

// Include header section
echo UX::makeHead($h, $n);

// Page info
echo UX::makeBreadcrumb(array(	'Course Listing &amp; Search'		=> '/courses.php' ));

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

// Page output
if ($_SERVER['QUERY_STRING'] !== 'override') {
    echo UX::grabPage('public/courses', null, true);
} else {
    echo UX::grabPage('public/courses_beta', null, true);
}

echo UX::grabPage('common/masthead', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>