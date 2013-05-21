<?php

/**
 * PTE listing
 *
 * @author  Yectep Studios <info@yectep.hk>
 * @version 30511
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
    $_laoshi->perms(8, 9, 10, 11, 12);
}

// Set default info
$h['title'] = 'PTE Request List';
$n['management'] = 'active';
$n['my_name'] = $_laoshi->staff['StaffName'];

// Include header section
echo UX::makeHead($h, $n, 'common/header_staff', $_laoshi->fetchNavPage());

// Course list array
$ptes = FamStu::getPTEList();
$p['pte_table'] = '';

foreach ($ptes as $pte) {

    $age = Courses::getAgeAtWeek($pte['ClassWeek'], $pte['StudentDOB']);
    $diff = FamStu::getAgeDifference($pte['ClassWeek'], $pte['StudentDOB'], $pte['ClassAgeMin']);

    if ($pte['EnrollStatus'] == 'pte_denied') {
        $p['pte_table'] .= "<tr>
            <td>".$pte['FamilyID'].".".$pte['StudentID']."</td>
            <td>".$pte['StudentName']."</td>
            <td><span class=\"tipped\" title=\"".date(DATE_FULL, strtotime($pte['StudentDOB']))."\">".date(DATE_SHORT, strtotime($pte['StudentDOB']))."</span></td>
            <td><a href=\"/staff/manage/course_view.php?cid=".$pte['CourseID']."\">".$pte['CourseTitle']."</a></td>
            <td><strong>".$pte['EnrollCount']."</strong>/".$pte['ClassEnrollMax']."</td>
            <td>".$pte['ClassAgeMin']."-".$pte['ClassAgeMax']."</td>
            <td>".$age."</td>
            <td>".$diff->y."y ".$diff->m."m ".$diff->d."d</td>
            <td><em class=\"muted\">PTE was denied</em></td>
        </tr>";
    } else {
        $p['pte_table'] .= "<tr>
            <td>".$pte['FamilyID'].".".$pte['StudentID']."</td>
            <td>".$pte['StudentName']."</td>
            <td><span class=\"tipped\" title=\"".date(DATE_FULL, strtotime($pte['StudentDOB']))."\">".date(DATE_SHORT, strtotime($pte['StudentDOB']))."</span></td>
            <td><a href=\"/staff/manage/course_view.php?cid=".$pte['CourseID']."\">".$pte['CourseTitle']."</a></td>
            <td><strong>".$pte['EnrollCount']."</strong>/".$pte['ClassEnrollMax']."</td>
            <td>".$pte['ClassAgeMin']."-".$pte['ClassAgeMax']."</td>
            <td>".$age."</td>
            <td>".$diff->y."y ".$diff->m."m ".$diff->d."d</td>
            <td><a href=\"pte_act.php?eid=".$pte['EnrollID']."&action=accept\">Accept</a> | <a href=\"pte_act.php?eid=".$pte['EnrollID']."&action=deny\">Deny</a></td>
        </tr>";
    }
}

// Page info
echo UX::makeBreadcrumb(array(  'Staff Portal'      => '/staff/dashboard.php', 'PTE List' => "/staff/manage/pte.php"));
echo UX::grabPage('staff/manage/pte', $p, true);

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

echo UX::grabPage('common/masthead_staff', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>

