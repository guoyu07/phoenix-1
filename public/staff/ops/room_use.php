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

// Set default info
$h['title'] = $_GET['rid'].' | Room Usage';
$n['ops'] = 'active';
$n['my_name'] = $_laoshi->staff['StaffName'];

try {
    $stmt = Data::prepare('SELECT co.CourseSubj, cl.CourseID, cl.ClassID AS ClassID, co.CourseTitle, st.StaffName, st.StaffCell, cl.ClassWeek, cl.ClassPeriodBegin, cl.ClassPeriodEnd, cl.TeacherID FROM courses co, classes cl, staff st WHERE co.CourseID = cl.CourseID AND cl.RoomID = :rid AND st.StaffID = cl.TeacherID AND cl.ClassStatus IN ("active", "full") ORDER BY cl.ClassWeek ASC, cl.ClassPeriodBegin ASC');
    $stmt->bindParam('rid', $_GET['rid']);
    $stmt->execute();
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die($e->getMessage());
}

$p['week_1'] = '';
$p['week_2'] = '';
$p['week_3'] = '';
$p['week_4'] = '';

$p['rid'] = $_GET['rid'];

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

    if ($e['ClassPeriodBegin'] == $e['ClassPeriodEnd']) $length = 'single';
    else $length = 'double';

    $p['week_'.$e['ClassWeek']] .= '<tr><td>'.$time_start.'-'.$time_end.'<span class="small muted" style="float:right;">'.$program.' '.(($length == 'single') ? $e['ClassPeriodBegin'] : $e['ClassPeriodBegin'].'-'.$e['ClassPeriodEnd']).'</span></td><td><a href="/staff/manage/class_edit.php?cid='.$e['ClassID'].'">'.$e['CourseTitle'].'</a> <span class="small muted" style="float:right;"><div class="course-colorbox course-cb-'.strtolower($e['CourseSubj']).'"></div> '.$e['CourseSubj'].str_pad($e['CourseID'], 3, '0', STR_PAD_LEFT).'.'.$e['ClassID'].'</span></td><td><a href="/staff/manage/view_teacher.php?tid='.$e['TeacherID'].'">'.$e['StaffName'].'</a> <span class="muted" style="float:right;">'.((strlen($e['StaffCell']) == 8) ? substr($e['StaffCell'], 0, 4).'-'.substr($e['StaffCell'], 4, 4) : $e['StaffCell']).'</span></td></tr>';
}

// Rooms
$stmt = Data::query('SELECT * FROM rooms');
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

$p['rooms'] = '';

foreach($rooms as $r) {
    $p['rooms'] .= '<option value="'.$r['RoomID'].'">'.$r['RoomName'].'</option>'."\n";
}


// Include header section
echo UX::makeHead($h, $n, 'common/header_staff', $_laoshi->fetchNavPage());

// Page info
echo UX::makeBreadcrumb(array(  'Staff Portal'      => '/staff/dashboard.php', 'Room '.$_GET['rid'].' Usage' => "/staff/ops/room_use.php?rid=".$_GET['rid']));
echo UX::grabPage('staff/ops/room_use', $p, true);

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

echo UX::grabPage('common/masthead_staff', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>

