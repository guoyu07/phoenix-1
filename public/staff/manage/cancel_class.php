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
$h['title'] = 'Cancel Class #'.$class['ClassID'];
$n['management'] = 'active';
$n['my_name'] = $_laoshi->staff['StaffName'];

// Make p array
$p['class_id'] = $class['ClassID'];
$p['course_id'] = $class['CourseID'];
$p['course_title'] = $class['CourseTitle'];
$p['teacher_name'] = $class['TeacherData']['TeacherName'];
$p['teacher_email'] = $class['TeacherData']['TeacherEmail'];


// Get enrollment
$stmt = Data::prepare('SELECT s.StudentID, s.StudentNamePreferred, s.StudentNameLast, s.StudentDOB, e.EnrollLETS FROM `enrollment` e, `students` s WHERE e.EnrollStatus = "enrolled" AND e.ClassID = :cid AND e.StudentID = s.StudentID ORDER BY s.StudentNamePreferred ASC, s.StudentNameLast ASC');
$stmt->bindParam('cid', $class['ClassID']);
$stmt->execute();
$enrollment = $stmt->fetchAll(PDO::FETCH_ASSOC);

$p['enroll_size'] = sizeof($enrollment);
$p['default_email'] = UX::grabPage('text_snippets/email_cancel_class', $class, false);

// Include header section
echo UX::makeHead($h, $n, 'common/header_staff', $_laoshi->fetchNavPage());

// Page info
echo UX::makeBreadcrumb(array(  'Staff Portal'      => '/staff/dashboard.php', 'Course Listing' => "/staff/manage/courses.php", $class['CourseTitle'] => "/staff/manage/course_view.php?cid=".$class['CourseID'],
    'Class #'.$class['ClassID'] => '/staff/manager/class_edit.php?cid='.$_REQUEST['cid']));
echo UX::grabPage('staff/manage/cancel_class', $p, true);

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

echo UX::grabPage('common/masthead_staff', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>

