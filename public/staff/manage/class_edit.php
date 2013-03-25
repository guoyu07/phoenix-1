<?php

/**
 * Course listing
 *
 * @author  Yectep Studios <info@yectep.hk>
 * @version 30122
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
    header('Location: /staff/index.php?msg=error_nologin');
    exit();
} else {
    $_laoshi = new Laoshi($_SESSION['SSOID']);
    $_laoshi->perms(6, 7);
}

// Get course information
$class = Courses::getClassById($_REQUEST['cid']);
$class['TeacherData'] = Courses::getTeacherById($class['TeacherID']);

// Make h array
$h['title'] = 'Class #'.$class['ClassID'] . ' | Class View';
$n['management'] = 'active';
$n['my_name'] = $_laoshi->staff['StaffName'];

// Make p array
$p['class_id'] = $class['ClassID'];
$p['course_id'] = $class['CourseID'];
$p['course_title'] = $class['CourseTitle'];
$p['teacher_name'] = $class['TeacherData']['TeacherName'];
$p['teacher_email'] = $class['TeacherData']['TeacherEmail'];
$p['w'.$class['ClassWeek'].'_selected'] = ' selected="selected"';
$p['pb'.strtolower($class['ClassPeriodBegin']).'_selected'] = ' selected="selected"';
$p['pe'.strtolower($class['ClassPeriodEnd']).'_selected'] = ' selected="selected"';
$p['minage'] = $class['ClassAgeMin'];
$p['maxage'] = $class['ClassAgeMax'];
$p['maxenroll'] = $class['ClassEnrollMax'];
$p['status'] = $class['ClassStatus'];
$p['curroom'] = $class['RoomID'];

$rooms = Courses::getRoomList();
$p['rooms'] = '';
foreach($rooms as $roomid => $rm) {
    $p['rooms'] .= '<option value="'.$roomid.'">'.$rm['name'].'</option>'."\n";
}

// Check greetings
if (($class['ClassPeriodBegin'] == 0) || ($class['ClassPeriodEnd'] == 0)) {
    $p['greeting'] = '<div class="alert alert-red"><span class="badge badge-red">Warning</span> I\'ve detected an error in this class form. Please double check to ensure that timeslots are correct. You can still release this class if required, but I can\'t guarantee seamless operation on the part of the parents.</div>';
} else {
    $p['greeting'] = '<div class="alert alert-green"><img src="/assets/icons/tick.png" /> This class looks perfect to me. If this class is public, parents will be able to sign up until it\'s full.</div>';
}

// Include header section
echo UX::makeHead($h, $n, 'common/header_staff', $_laoshi->fetchNavPage());

// Page info
echo UX::makeBreadcrumb(array(  'Staff Portal'      => '/staff/dashboard.php', 'Course Listing' => "/staff/manage/courses.php", $class['CourseTitle'] => "/staff/manage/course_view.php?cid=".$class['CourseID'],
    'Class #'.$class['ClassID'] => '/staff/manager/class_edit.php?cid='.$_REQUEST['cid']));
echo UX::grabPage('staff/manage/class_edit', $p, true);

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

echo UX::grabPage('common/masthead_staff', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>

