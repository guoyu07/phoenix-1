<?php

/**
 * Course applications
 *
 * @author  Yectep Studios <info@yectep.hk>
 * @version 30104
 * @package Plume
 * @subpackage Staff
 */


define('PTP',   '../../../private/');
define('PHX_SCRIPT_TYPE',   'HTML');
define('PHX_UX',        true);
define('PHX_LAOSHI',    true);
define('PHX_COURSES',   true);


// Include common ignition class
require_once(PTP . 'php/ignition.php');

// We require a staff login for this page
if (!ACL::checkLogin('staff')) {
    header('Location: ./index.php?msg=error_nologin');
    exit();
} else {
    $_laoshi = new Laoshi($_SESSION['SSOID']);
    $_laoshi->perms(6, 7, 8);
}

// Triage and get default staff page
$h['title'] = 'My Dashboard';
$n['management'] = 'active';
$n['my_name'] = $_laoshi->staff['StaffName'];

// Include header section
echo UX::makeHead($h, $n, 'common/header_staff', $_laoshi->fetchNavPage());

$p["courses_submitted"] = '';
$p["courses_saved"] = '';

$submitted = Courses::getApps('submitted');
$p["count_submitted"] = ((!$submitted) ? 0 : sizeof($submitted));
$saved = Courses::getApps('saved');
$p["count_saved"] = ((!$saved) ? 0 : sizeof($saved));

foreach($submitted as $course) {
    $p["courses_submitted"] .= UX::grabPage('staff/manage/apps_submitted', $course, true);
}

foreach($saved as $course) {
    $p["courses_saved"] .= UX::grabPage('staff/manage/apps_saved', $course, true);
}

// Page info
echo UX::makeBreadcrumb(array(  'Staff Portal'      => '/staff/index.php', 'My Dashboard' => "/staff/dashboard.php"));
echo UX::grabPage('staff/manage/applications', $p, true);

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

echo UX::grabPage('common/masthead_staff', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>

