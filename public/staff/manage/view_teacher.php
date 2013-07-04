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

// Get info
$stmt = Data::prepare('SELECT st.*, obj.* FROM staff st, sso_objects obj WHERE obj.ObjID = st.ObjID AND st.StaffID = :sid');
$stmt->bindParam('sid', $_GET['tid'], PDO::PARAM_INT);
$stmt->execute();
$teacher = $stmt->fetch(PDO::FETCH_ASSOC);

// Set default info
$h['title'] = $teacher['StaffName'].' | Teacher Profile';
$n['ops'] = 'active';
$n['my_name'] = $_laoshi->staff['StaffName'];

$stmt = Data::prepare("SELECT co.CourseSubj, co.CourseID, cl.ClassID, co.CourseTitle, cl.ClassWeek, cl.ClassPeriodBegin, cl.ClassPeriodEnd, cl.RoomID, (SELECT count(EnrollID) from enrollment where EnrollStatus = 'enrolled' AND ClassID = cl.ClassID) as EnrollCount, cl.ClassEnrollMax FROM courses co, classes cl WHERE cl.TeacherID = :tid AND co.CourseID = cl.CourseID AND cl.ClassStatus IN ('active', 'full')");
$stmt->bindParam('tid', $_GET['tid'], PDO::PARAM_INT);
$stmt->execute();
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$p['staff_name'] = $teacher['StaffName'];
$p['sso_id'] = $teacher['ObjID'];
$p['email'] = '<a href="mailto:'.$teacher['ObjEmail'].'">'.$teacher['ObjEmail'].'</a>';
$p['cell'] = ((strlen($teacher['StaffCell']) == 8) ? '<span class="muted">+852</span> '.substr($teacher['StaffCell'], 0, 4).'-'.substr($teacher['StaffCell'], 4, 4) : $teacher['StaffCell']);
$p['week_1'] = '';
$p['week_2'] = '';
$p['week_3'] = '';
$p['week_4'] = '';

foreach($classes as $e) {

    switch ($e['ClassPeriodBegin']) {
        case '1':
            $time_start = '09:00';
            $program = 'Pd';
        break;
        case '2':
            $time_start = '10:00';
            $program = 'Pd';
        break;
        case '3':
            $time_start = '11:30';
            $program = 'Pd';
        break;
        case '4':
            $time_start = '12:30';
            $program = 'Pd';
        break;
        case 'A':
            $time_start = '09:30';
            $program = 'Sn';
        break;
        case 'B':
            $time_start = '12:00';
            $program = 'Sn';
        break;
        case 'C':
            $time_start = '14:30';
            $program = 'Sn';
        break;
    }

    switch ($e['ClassPeriodEnd']) {
        case '1':
            $time_end = '09:55';
        break;
        case '2':
            $time_end = '10:55';
        break;
        case '3':
            $time_end = '12:25';
        break;
        case '4':
            $time_end = '13:25';
        break;
        case 'A':
            $time_end = '11:30';
        break;
        case 'B':
            $time_end = '14:00';
        break;
        case 'C':
            $time_end = '16:30';
        break;
    }

    if ($e['ClassPeriodBegin'] == $e['ClassPeriodEnd']) $length = $e['ClassPeriodBegin'];
    else $length = $e['ClassPeriodBegin'].'-'.$e['ClassPeriodEnd'];

    $p['week_'.$e['ClassWeek']] .= '<tr><td>'.$time_start.'-'.$time_end.' <span class="small muted" style="float:right;">'.$program.' '.$length.'</span></td><td><a href="/staff/manage/class_edit.php?cid='.$e['ClassID'].'">'.$e['CourseTitle'].'</a> in Room <a href="/staff/ops/room_use.php?rid='.$e['RoomID'].'">'.$e['RoomID'].'</a> <span style="float:right;" class="muted small"><div class="course-colorbox course-cb-'.strtolower($e['CourseSubj']).'"></div> '.$e['CourseSubj'].str_pad($e['CourseID'], 3, '0', STR_PAD_LEFT).' (<span style="color:#222;">'.$e['EnrollCount'].'</span>/'.$e['ClassEnrollMax'].')</span></td></tr>';
}

// Include header section
echo UX::makeHead($h, $n, 'common/header_staff', $_laoshi->fetchNavPage());

// Page info
echo UX::makeBreadcrumb(array(  'Staff Portal'      => '/staff/dashboard.php', 'Profile: '.$p['staff_name'] => "/staff/manage/view_teacher.php?tid=".$_GET['tid']));
echo UX::grabPage('staff/manage/view_teacher', $p, true);

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

echo UX::grabPage('common/masthead_staff', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>

