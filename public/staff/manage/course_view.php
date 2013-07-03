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
$course = Courses::getCourseById($_REQUEST['cid']);
$course['TeacherData'] = Courses::getTeacherById($course['TeacherLead']);
$course['ClassData'] = Courses::getClassesOfCourseById($_REQUEST['cid']);
$course['CourseDecription'] = Common::cleanse($course['CourseDescription']);

$p['offerings'] = '';
$rooms = Courses::getRoomList();
$p['rooms'] = '';
foreach($rooms as $roomid => $rm) {
    $p['rooms'] .= '<option value="'.$roomid.'">'.$rm['name'].'</option>'."\n";
}

// Build offerings table
foreach($course['ClassData'] as $class) {
    $teacher = Courses::getTeacherById($class['TeacherID']);
    $p['offerings'] .= "<tr data-classnum=\"".$class['ClassID']."\"><td><div class=\"course-colorbox course-cb-".strtolower($course['CourseSubj'])."\"></div> ".$course['CourseID'].".".$class['ClassID']."</td>
    <td><a href=\"/staff/manage/view_teacher.php?tid=".$teacher['TeacherID']."\">".$teacher['TeacherName']."</a></td>
    <td>".$class['ClassWeek']."</td>
    <td>".(($class['ClassPeriodBegin'] == $class['ClassPeriodEnd']) ? $class['ClassPeriodBegin'] : $class['ClassPeriodBegin']."-".$class['ClassPeriodEnd'])."</td>
    <td><a href=\"/staff/ops/room_use.php?rid=".$class['RoomID']."\">".$class['RoomID']."</a></td>
    <td><strong>".$class['EnrollCount']."</strong><span class=\"muted\">/".$class['ClassEnrollMax']."</td>
    <td id=\"status-".$class['ClassID']."\">".$class['ClassHtmlStatus']."</td>
    <td><a href=\"./class_edit.php?cid=".$class['ClassID']."\">Edit</a>".(($class['EnrollCount'] > 0) ? "" : " | <a href=\"./class_edit.php?cid=".$class['ClassID']."&action=cancel\" class=\"cancel-class\">Cancel</a>")."</td></tr>";
}

$h['title'] = $course['CourseTitle'] . ' | Course View';
$n['management'] = 'active';
$n['my_name'] = $_laoshi->staff['StaffName'];

// Course info
$p['course_subject'] = $course['CourseSubj'];
$p['course_formatted_id'] = str_pad($course['CourseID'], 3, '0', STR_PAD_LEFT);
$p['course_id'] = $course['CourseID'];
$p['course_title'] = $course['CourseTitle'];
$p['course_diff'] = $course['CourseDifficulty'];
$p['course_diff_text'] = Courses::getDifficulty($course['CourseDifficulty']);
$p['course_rmks'] = $course['CourseRemarks'];
$p['course_desc'] = $course['CourseDescription'];
$p['course_synop'] = ((strlen($course['CourseSynop']) == 0) ? '(None provided)' : $course['CourseSynop']);
$p['course_prereqs'] = ((strlen($course['CoursePrereqs']) == 0) ? '(None provided)' : $course['CoursePrereqs']);
$p['course_outcomes'] = ((strlen($course['CourseOutcomes']) == 0) ? '(None provided)' : $course['CourseOutcomes']);
$p['lead_instructor_id'] = $course['TeacherData']['TeacherID'];
$p['lead_instructor_name'] = $course['TeacherData']['TeacherName'];
$p['lead_instructor_email'] = $course['TeacherData']['TeacherEmail'];
$p['computers_active'] = (($course['CourseComputers'] == 0) ? 'inactive' : 'active');
$p['instruments_active'] = (($course['CourseInstruments'] == 0) ? 'inactive' : 'active');
$p['activity_active'] = (($course['CourseActivity'] == 0) ? 'inactive' : 'active');
$p['age_active'] = (($course['CourseEnforceAge'] == 0) ? 'inactive' : 'active');
$p['enroll_active'] = (($course['CourseEnforceEnroll'] == 0) ? 'inactive' : 'active');
$p['public_active'] = (($course['CourseFlagsPublic'] == 0) ? 'inactive' : 'active');
$p['addon_active'] = (($course['CourseFeeAddon'] == 0) ? 'inactive' : 'active');

// Include header section
echo UX::makeHead($h, $n, 'common/header_staff', $_laoshi->fetchNavPage());

// Page info
echo UX::makeBreadcrumb(array(  'Staff Portal'      => '/staff/dashboard.php', 'Course Listing' => "/staff/manage/courses.php", $course['CourseTitle'] => "/staff/manage/course_view.php?cid=".$_REQUEST['cid']));
echo UX::grabPage('staff/manage/course_view', $p, true);

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

echo UX::grabPage('common/masthead_staff', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>

