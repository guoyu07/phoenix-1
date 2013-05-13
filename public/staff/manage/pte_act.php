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

if (array_key_exists('action', $_GET) && array_key_exists('eid', $_GET)) {
    // Confirm first!
    $stmt = Data::prepare('SELECT * FROM `enrollment` WHERE `EnrollID` = :eid LIMIT 1');
    $stmt->bindParam('eid', $_GET['eid'], PDO::PARAM_INT);
    $stmt->execute();

    $enrollment = $stmt->fetch(PDO::FETCH_ASSOC);

    $class = Courses::getClassById($enrollment['ClassID']);

    if (sizeof($enrollment) > 1) {
        $stmt = Data::prepare('SELECT * FROM `students` WHERE `StudentID` = :stuid LIMIT 1');
        $stmt->bindParam('stuid', $enrollment['StudentID'], PDO::PARAM_INT);
        $stmt->execute();
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = Data::prepare('SELECT e.* FROM `enrollment` e, `classes` c WHERE c.ClassWeek = :week AND (c.ClassPeriodBegin = :period OR c.ClassPeriodEnd = :period) AND c.ClassID = e.ClassID AND e.StudentID = :stuid  AND e.EnrollStatus = "enrolled"');
        $stmt->bindParam('week', $class['ClassWeek']);
        $stmt->bindParam('period', $class['ClassPeriodBegin']);
        $stmt->bindParam('stuid', $student['StudentID']);
        $stmt->execute();
        $period_begin = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($period_begin as $i => $overlap) {
            $period_begin[$i]['ClassData'] = Courses::getClassById($overlap['ClassID']);
        }

        $stmt = Data::prepare('SELECT e.* FROM `enrollment` e, `classes` c WHERE c.ClassWeek = :week AND (c.ClassPeriodBegin = :period OR c.ClassPeriodEnd = :period) AND c.ClassID = e.ClassID AND e.StudentID = :stuid  AND e.EnrollStatus = "enrolled"');
        $stmt->bindParam('week', $class['ClassWeek']);
        $stmt->bindParam('period', $class['ClassPeriodEnd']);
        $stmt->bindParam('stuid', $student['StudentID']);
        $stmt->execute();
        $period_end = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Set default info
        $h['title'] = 'Confirm PTE';
        $n['management'] = 'active';
        $n['my_name'] = $_laoshi->staff['StaffName'];

        $p['var_dump'] = var_export($enrollment, true);

        $p['pte_course_title'] = $class['CourseTitle'];
        $p['student_name'] = $student['StudentNamePreferred'].' '.$student['StudentNameLast'];

        $output = UX::grabPage('staff/manage/pte_act_confirm', $p, true);

    } else {
        var_dump($enrollment);
    }

}


// Include header section
echo UX::makeHead($h, $n, 'common/header_staff', $_laoshi->fetchNavPage());

// Page info
echo UX::makeBreadcrumb(array(  'Staff Portal'      => '/staff/dashboard.php', 'PTE List' => "/staff/manage/pte.php", 'Decision' => "javascript:;"));
echo $output;

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

echo UX::grabPage('common/masthead_staff', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);


?>

