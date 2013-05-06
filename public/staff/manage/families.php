<?php

/**
 * Family listing
 *
 * @author  Yectep Studios <info@yectep.hk>
 * @version 30502
 * @package Plume
 * @subpackage Staff
 */


define('PTP',   '../../../private/');
define('PHX_SCRIPT_TYPE',   'HTML');
define('PHX_UX',        true);
define('PHX_LAOSHI',    true);
define('PHX_COURSES',   true);
define('PHX_STUDENT',   true);


// Include common ignition class
require_once(PTP . 'php/ignition.php');

// We require a staff login for this page
if (!ACL::checkLogin('staff')) {
    header('Location: /staff/index.php?msg=error_nologin');
    exit();
} else {
    $_laoshi = new Laoshi($_SESSION['SSOID']);
    $_laoshi->perms(8, 9, 10, 11, 12);
}

// Set default info
$h['title'] = 'Families';
$n['management'] = 'active';
$n['my_name'] = $_laoshi->staff['StaffName'];

// Include header section
echo UX::makeHead($h, $n, 'common/header_staff', $_laoshi->fetchNavPage());

// Course list array
$families = FamStu::getFamilies();
$p['family_json'] = json_encode($families);
$p['number_of_families'] = sizeof($families);

// Page info
echo UX::makeBreadcrumb(array(  'Staff Portal'      => '/staff/dashboard.php', 'Family Account List' => "/staff/manage/courses.php"));
echo UX::grabPage('staff/manage/families', $p, true);

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

echo UX::grabPage('common/masthead_staff', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>

