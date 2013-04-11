<?php

/**
 * Course view page
 *
 * @author  Yectep Studios <info@yectep.hk>
 * @version 30405
 * @package Plume
 */


define('PTP',   '../private/');
define('PHX_SCRIPT_TYPE',   'HTML');
define('PHX_UX',        true);
define('PHX_LAOSHI',    true);
define('PHX_COURSES',   true);


// Include common ignition class
require_once(PTP . 'php/ignition.php');

// Get course information
$course = Courses::getCourseById($_REQUEST['id']);

if ((!$course) || ($course['CourseFlagsPublic'] == '0')) {
    header("Location: /courses.php");
    exit();
}

// Decide on program
if (($course['CourseSubj'] == 'ARTS') || ($course['CourseSubj'] == 'LANG') || ($course['CourseSubj'] == 'MSCT') || ($course['CourseSubj'] == 'PHED')) {
    $course['CourseProgram'] = 'summer';
} else {
    $course['CourseProgram'] = 'academic';
}

$course['TeacherData'] = Courses::getTeacherById($course['TeacherLead']);
$course['ClassData'] = Courses::getClassesOfCourseById($_REQUEST['id']);
$course['CourseDecription'] = Common::cleanse($course['CourseDescription']);
$course['CourseOutcomes'] = Common::cleanse($course['CourseOutcomes']);

// Build offerings table
$p['offerings'] = '';
foreach($course['ClassData'] as $class) {
    $teacher = Courses::getTeacherById($class['TeacherID']);
    if (($class['ClassStatus'] == 'active') || ($class['ClassStatus'] == 'full')) {
        $p['offerings'] .= "<tr><td>".$course['CourseID'].".".$class['ClassID']."</td>
        <td>".(($teacher['TeacherName'] == '') ? '<em class="muted">To be confirmed</em>' : $teacher['TeacherName'])."</td>
        <td><span class=\"badge badge-green\">Week ".$class['ClassWeek']."</span></td>
        <td><span class=\"badge badge-blue\">".(($class['ClassPeriodBegin'] == $class['ClassPeriodEnd']) ? "Period ".$class['ClassPeriodBegin'] : "Periods ".$class['ClassPeriodBegin']."-".$class['ClassPeriodEnd'])."</span></td>
        <td>".$class['ClassAgeMin']."-".$class['ClassAgeMax']."</td></tr>";
    }
}

$h['title'] = $course['CourseTitle'] . ' | Course View';
$n['courses'] = 'active';

// Course info
$p['course_program'] = (($course['CourseProgram'] == 'summer') ? 'Summer Program' : 'Academic Program');
$p['course_subject'] = $course['CourseSubj'];
$p['course_subj_lc'] = strtolower($course['CourseSubj']);
$p['course_formatted_id'] = str_pad($course['CourseID'], 3, '0', STR_PAD_LEFT);
$p['course_id'] = $course['CourseID'];
$p['course_loi'] = $course['CourseLOI'];
$p['course_loi_text'] = (($course['CourseLOI'] == 'en') ? 'English' : (($course['CourseLOI'] == 'zh') ? 'Putonghua (Mandarin)' : 'Bilingual (Putonghua &amp; English'));
$p['course_title'] = $course['CourseTitle'];
$p['course_diff'] = $course['CourseDifficulty'];
$p['course_diff_text'] = Courses::getDifficulty($course['CourseDifficulty']);
$p['course_desc'] = $course['CourseDescription'];
$p['course_synop'] = ((strlen($course['CourseSynop']) == 0) ? '(None provided)' : $course['CourseSynop']);
$p['course_prereqs'] = ((strlen($course['CoursePrereqs']) == 0) ? '(None provided)' : $course['CoursePrereqs']);
$p['course_outcomes'] = ((strlen($course['CourseOutcomes']) == 0) ? '(None provided)' : $course['CourseOutcomes']);
$p['lead_instructor_id'] = $course['TeacherData']['TeacherID'];
$p['lead_instructor_name'] = $course['TeacherData']['TeacherName'];
$p['lead_instructor_email'] = $course['TeacherData']['TeacherEmail'];
$p['computers_active'] = (($course['CourseComputers'] == 0) ? 'hide' : 'active');
$p['instruments_active'] = (($course['CourseInstruments'] == 0) ? 'hide' : 'active');
$p['activity_active'] = (($course['CourseActivity'] == 0) ? 'hide' : 'active');
$p['age_active'] = (($course['CourseEnforceAge'] == 0) ? 'hide' : 'active');
$p['enroll_active'] = (($course['CourseEnforceEnroll'] == 0) ? 'hide' : 'active');
$p['public_active'] = (($course['CourseFlagsPublic'] == 0) ? 'hide' : 'active');
$p['addon_active'] = (($course['CourseFeeAddon'] == 0) ? 'hide' : 'active');
$p['note'] = (($course['CourseProgram'] == 'academic') ? '<div class="alert alert-red">Some Academic Program courses are still subject to change. Please ensure you re-check all information during the registration period. Thank you for your attention!</div>' : '');


// Include header section
echo UX::makeHead($h, $n);

// Page info
echo UX::makeBreadcrumb(array(  'Course Listing &amp; Search'       => '/courses.php', $course['CourseTitle'] => '/view_course.php?id='.$_REQUEST['id'] ));

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

// Page output
echo UX::grabPage('public/view_course', $p, true);

echo UX::grabPage('common/masthead', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>

