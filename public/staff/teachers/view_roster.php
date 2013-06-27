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
    // $_laoshi->perms(6, 7);
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

// Get enrollment
$stmt = Data::prepare('SELECT s.*, f.FamilyName FROM `enrollment` e, `students` s, `families` f WHERE e.EnrollStatus = "enrolled" AND e.ClassID = :cid AND e.StudentID = s.StudentID AND f.FamilyID = s.FamilyID AND s.StudentSubmitted = 1 ORDER BY s.StudentNamePreferred ASC, s.StudentNameLast ASC');
$stmt->bindParam('cid', $class['ClassID']);
$stmt->execute();
$enrollment = $stmt->fetchAll(PDO::FETCH_ASSOC);

$p['enroll_table'] = '';

if (sizeof($enrollment) > 0) {
    foreach ($enrollment as $stu) {
        // Check student medical condition
        $condition = str_replace(array('none', 'n/a', 'nil'), array('', '', ''), $stu['StudentMedCondition']);
        if (strlen(trim($condition)) == 0) {
            $p['enroll_table'] .= '<tr><td>'.strtoupper($stu['StudentNameLast']).', '.$stu['StudentNamePreferred'].'</td><td>'.date(DATE_FULL, strtotime($stu['StudentDOB'])).'</td><td>'.ucwords($stu['FamilyName']).'</td></tr>';
        } else {
            $p['enroll_table'] .= '<tr><td><img src="/assets/icons/exclamation.png" /> '.strtoupper($stu['StudentNameLast']).', '.$stu['StudentNamePreferred'].'<br /><span class="badge badge-red">Medical Condition</span> <span class="red">'.$condition.'</span></td><td>'.date(DATE_FULL, strtotime($stu['StudentDOB'])).'</td><td>'.ucwords($stu['FamilyName']).'</td></tr>';
        }
    }
} else {
    $p['enroll_table'] = '<tr><td colspan="3"><em class="muted">Sorry, there are no students currently enrolled in this class.</em></td></tr>';
}

// Include header section
echo UX::makeHead($h, $n, 'common/header_staff', $_laoshi->fetchNavPage());

// Page info
echo UX::makeBreadcrumb(array(  'Teacher Dashboard'      => '/staff/dashboard.php', $class['CourseTitle'].' Roster' => "/staff/teachers/view_roster.php?cid=".$class['CourseID']));
echo UX::grabPage('staff/teachers/view_roster', $p, true);

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

echo UX::grabPage('common/masthead_staff', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>

