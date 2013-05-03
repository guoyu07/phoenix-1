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
    $_laoshi->perms(6, 7);
}

// Set default info
$h['title'] = 'Course List';
$n['management'] = 'active';
$n['my_name'] = $_laoshi->staff['StaffName'];

// Include header section
echo UX::makeHead($h, $n, 'common/header_staff', $_laoshi->fetchNavPage());

// Course list array
$courses = Courses::getCourseList();
foreach($courses as $num => $course) {
    $classes = Courses::getClassesOfCourseById($course['CourseID']);
    $courses[$num]['ClassData'] = $classes;
    $courses[$num]['ClassCount'] = (string) sizeof($classes);
    $courses[$num]['CourseDescription'] = Common::cleanse($course['CourseDescription']);
    $courses[$num]['CourseCode'] = strtoupper($course['CourseSubj']).str_pad($course['CourseID'], 3, '0', STR_PAD_LEFT);
    $courses[$num]['TeacherData'] = Courses::getTeacherById($course['TeacherLead']);

    $totalEnrolled = 0;
    $totalSpaces = 0;

    foreach($classes as $class) {
        $totalEnrolled += $class['EnrollCount'];
        $totalSpaces += $class['ClassEnrollMax'];
    }

    $courses[$num]['TotalSpacesAvailable'] = (string) $totalSpaces;
    $courses[$num]['TotalEnrollCount'] = (string) $totalEnrolled;
}
$p['number_of_courses'] = sizeof($courses);
$p['courseJson'] = json_encode($courses);

// Page info
echo UX::makeBreadcrumb(array(  'Staff Portal'      => '/staff/dashboard.php', 'Course Listing' => "/staff/manage/courses.php"));
echo UX::grabPage('staff/manage/courses', $p, true);

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

echo UX::grabPage('common/masthead_staff', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>

