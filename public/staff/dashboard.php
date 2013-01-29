<?php

/**
 * Staff dashboard
 *
 * @author  Yectep Studios <info@yectep.hk>
 * @version 30104
 * @package Plume
 * @subpackage Staff
 */


define('PTP',   '../../private/');
define('PHX_SCRIPT_TYPE',   'HTML');
define('PHX_UX',        true);
define('PHX_LAOSHI',    true);


// Include common ignition class
require_once(PTP . 'php/ignition.php');

// We require a staff login for this page
if (!ACL::checkLogin('staff')) {
    header('Location: ./index.php?msg=error_nologin');
    exit();
} else {
    $_laoshi = new Laoshi($_SESSION['SSOID']);
}

// Triage and get default staff page
$h['title'] = 'My Dashboard';
$n['dashboard'] = 'active';
$n['my_name'] = $_laoshi->staff['StaffName'];

// Default page
$d['my_name'] = $_laoshi->staff['StaffName'];
$d['my_email'] = $_laoshi->sso['ObjEmail'];
$d['pass_age'] = Common::relativeTime(strtotime($_laoshi->sso['ObjPassUpdateTS']));
$d['pev'] = '<span class="muted">'.$_laoshi->sso['ObjHash'].'</span>';
$d['type'] = $_laoshi->sso['ObjType']['TypeName'];
$d['last_visit'] = Common::relativeTime(strtotime($_laoshi->sso['ObjLLTS']));
$d['account_cts'] = Common::relativeTime(strtotime($_laoshi->sso['ObjCTS']));

$p['content'] = UX::grabPage($_laoshi->fetchDefaultPage(), $d);


// Include header section
echo UX::makeHead($h, $n, 'common/header_staff', $_laoshi->fetchNavPage());

// Page info
echo UX::makeBreadcrumb(array(  'Staff Portal'      => '/staff/index.php', 'My Dashboard' => "/staff/dashboard.php"));
echo UX::grabPage('staff/dashboard', $p, true);

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

echo UX::grabPage('common/masthead_staff', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>

