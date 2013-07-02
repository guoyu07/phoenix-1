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

$stmt = Data::prepare("select co.*, cl.* from courses co, classes cl where co.CourseID = cl.CourseID and cl.TeacherID = :lead and cl.ClassStatus IN ('active', 'full') order by cl.ClassWeek asc, cl.ClassPeriodBegin asc");
$stmt->bindParam('lead', $_laoshi->staff['StaffID']);
$stmt->execute();
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$d['magic'] = var_export($classes, true);

$d['class_w1'] = '';
$d['class_w2'] = '';
$d['class_w3'] = '';
$d['class_w4'] = '';

$d['reg_w1'] = '';
$d['reg_w2'] = '';
$d['reg_w3'] = '';
$d['reg_w4'] = '';

foreach($classes as $class) {
    $d['class_w'.$class['ClassWeek']] .= '<strong><span class="badge">Period '.$class['ClassPeriodBegin'].'-'.$class['ClassPeriodEnd'].'</span> <a href="/staff/teachers/view_roster.php?cid='.$class['ClassID'].'">'.$class['CourseTitle'].'</a></strong><br />';
    $d['reg_w'.$class['ClassWeek']] .= '<strong><span class="badge">Period '.$class['ClassPeriodBegin'].'-'.$class['ClassPeriodEnd'].'</span> <a href="/staff/teachers/registration.php?cid='.$class['ClassID'].'">'.$class['CourseTitle'].'</a></strong><br />';

}

$d['day'] = date('l');
$d['week'] = Common::getCurrentWeek();

$p['content'] = UX::grabPage($_laoshi->fetchDefaultPage(), $d);
$p['staff_id'] = $_laoshi->staff['StaffID'];



// Include header section
echo UX::makeHead($h, $n, 'common/header_staff', $_laoshi->fetchNavPage());

// Page info
echo UX::makeBreadcrumb(array(  'Staff Portal'      => '/staff/index.php', 'My Dashboard' => "/staff/dashboard.php"));

if ($_laoshi->staff['StaffCell'] == '0') {
    echo UX::grabPage('staff/add_cell', $p, true);
} else {
    echo UX::grabPage('staff/dashboard', $p, true);
}

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

echo UX::grabPage('common/masthead_staff', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>

