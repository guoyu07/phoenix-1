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
    $_laoshi->perms(8, 9, 10, 11, 12);
}

// Set default info
$h['title'] = 'Student List';
$n['management'] = 'active';
$n['my_name'] = $_laoshi->staff['StaffName'];

// Include header section
echo UX::makeHead($h, $n, 'common/header_staff', $_laoshi->fetchNavPage());

// Course list array
$stmt = Data::query("SELECT s.*, f.`FamilyName` FROM students s, families f WHERE s.FamilyID = f.FamilyID ORDER BY s.StudentNamePreferred ASC, s.StudentNameLast ASC");
$famData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// foreach($famData as $aid => $stu) {
//     $stmt = Data::prepare("SELECT
//     (SELECT e.ClassID FROM enrollment e, classes c WHERE c.ClassID = e.ClassID AND e.EnrollStatus = 'enrolled' AND c.ClassWeek = 1 AND (c.ClassPeriodBegin = 1 OR c.ClassPeriodEnd = 1) AND e.StudentID = :stuid) as W1P1,
//     (SELECT e.ClassID FROM enrollment e, classes c WHERE c.ClassID = e.ClassID AND e.EnrollStatus = 'enrolled' AND c.ClassWeek = 1 AND (c.ClassPeriodBegin = 2 OR c.ClassPeriodEnd = 2) AND e.StudentID = :stuid) as W1P2,
//     (SELECT e.ClassID FROM enrollment e, classes c WHERE c.ClassID = e.ClassID AND e.EnrollStatus = 'enrolled' AND c.ClassWeek = 1 AND (c.ClassPeriodBegin = 3 OR c.ClassPeriodEnd = 3) AND e.StudentID = :stuid) as W1P3,
//     (SELECT e.ClassID FROM enrollment e, classes c WHERE c.ClassID = e.ClassID AND e.EnrollStatus = 'enrolled' AND c.ClassWeek = 1 AND (c.ClassPeriodBegin = 4 OR c.ClassPeriodEnd = 4) AND e.StudentID = :stuid) as W1P4,
//     (SELECT e.ClassID FROM enrollment e, classes c WHERE c.ClassID = e.ClassID AND e.EnrollStatus = 'enrolled' AND c.ClassWeek = 1 AND (c.ClassPeriodBegin = 'A' OR c.ClassPeriodEnd = 'A') AND e.StudentID = :stuid) as W1PC,
//     (SELECT e.ClassID FROM enrollment e, classes c WHERE c.ClassID = e.ClassID AND e.EnrollStatus = 'enrolled' AND c.ClassWeek = 1 AND (c.ClassPeriodBegin = 'B' OR c.ClassPeriodEnd = 'B') AND e.StudentID = :stuid) as W1PB,
//     (SELECT e.ClassID FROM enrollment e, classes c WHERE c.ClassID = e.ClassID AND e.EnrollStatus = 'enrolled' AND c.ClassWeek = 1 AND (c.ClassPeriodBegin = 'C' OR c.ClassPeriodEnd = 'C') AND e.StudentID = :stuid) as W1PC,
//     (SELECT e.ClassID FROM enrollment e, classes c WHERE c.ClassID = e.ClassID AND e.EnrollStatus = 'enrolled' AND c.ClassWeek = 2 AND (c.ClassPeriodBegin = 1 OR c.ClassPeriodEnd = 1) AND e.StudentID = :stuid) as W2P1,
//     (SELECT e.ClassID FROM enrollment e, classes c WHERE c.ClassID = e.ClassID AND e.EnrollStatus = 'enrolled' AND c.ClassWeek = 2 AND (c.ClassPeriodBegin = 2 OR c.ClassPeriodEnd = 2) AND e.StudentID = :stuid) as W2P2,
//     (SELECT e.ClassID FROM enrollment e, classes c WHERE c.ClassID = e.ClassID AND e.EnrollStatus = 'enrolled' AND c.ClassWeek = 2 AND (c.ClassPeriodBegin = 3 OR c.ClassPeriodEnd = 3) AND e.StudentID = :stuid) as W2P3,
//     (SELECT e.ClassID FROM enrollment e, classes c WHERE c.ClassID = e.ClassID AND e.EnrollStatus = 'enrolled' AND c.ClassWeek = 2 AND (c.ClassPeriodBegin = 4 OR c.ClassPeriodEnd = 4) AND e.StudentID = :stuid) as W2P4,
//     (SELECT e.ClassID FROM enrollment e, classes c WHERE c.ClassID = e.ClassID AND e.EnrollStatus = 'enrolled' AND c.ClassWeek = 2 AND (c.ClassPeriodBegin = 'A' OR c.ClassPeriodEnd = 'A') AND e.StudentID = :stuid) as W2PC,
//     (SELECT e.ClassID FROM enrollment e, classes c WHERE c.ClassID = e.ClassID AND e.EnrollStatus = 'enrolled' AND c.ClassWeek = 2 AND (c.ClassPeriodBegin = 'B' OR c.ClassPeriodEnd = 'B') AND e.StudentID = :stuid) as W2PB,
//     (SELECT e.ClassID FROM enrollment e, classes c WHERE c.ClassID = e.ClassID AND e.EnrollStatus = 'enrolled' AND c.ClassWeek = 2 AND (c.ClassPeriodBegin = 'C' OR c.ClassPeriodEnd = 'C') AND e.StudentID = :stuid) as W2PC,
//     (SELECT e.ClassID FROM enrollment e, classes c WHERE c.ClassID = e.ClassID AND e.EnrollStatus = 'enrolled' AND c.ClassWeek = 3 AND (c.ClassPeriodBegin = 1 OR c.ClassPeriodEnd = 1) AND e.StudentID = :stuid) as W3P1,
//     (SELECT e.ClassID FROM enrollment e, classes c WHERE c.ClassID = e.ClassID AND e.EnrollStatus = 'enrolled' AND c.ClassWeek = 3 AND (c.ClassPeriodBegin = 2 OR c.ClassPeriodEnd = 2) AND e.StudentID = :stuid) as W3P2,
//     (SELECT e.ClassID FROM enrollment e, classes c WHERE c.ClassID = e.ClassID AND e.EnrollStatus = 'enrolled' AND c.ClassWeek = 3 AND (c.ClassPeriodBegin = 3 OR c.ClassPeriodEnd = 3) AND e.StudentID = :stuid) as W3P3,
//     (SELECT e.ClassID FROM enrollment e, classes c WHERE c.ClassID = e.ClassID AND e.EnrollStatus = 'enrolled' AND c.ClassWeek = 3 AND (c.ClassPeriodBegin = 4 OR c.ClassPeriodEnd = 4) AND e.StudentID = :stuid) as W3P4,
//     (SELECT e.ClassID FROM enrollment e, classes c WHERE c.ClassID = e.ClassID AND e.EnrollStatus = 'enrolled' AND c.ClassWeek = 3 AND (c.ClassPeriodBegin = 'A' OR c.ClassPeriodEnd = 'A') AND e.StudentID = :stuid) as W3PC,
//     (SELECT e.ClassID FROM enrollment e, classes c WHERE c.ClassID = e.ClassID AND e.EnrollStatus = 'enrolled' AND c.ClassWeek = 3 AND (c.ClassPeriodBegin = 'B' OR c.ClassPeriodEnd = 'B') AND e.StudentID = :stuid) as W3PB,
//     (SELECT e.ClassID FROM enrollment e, classes c WHERE c.ClassID = e.ClassID AND e.EnrollStatus = 'enrolled' AND c.ClassWeek = 3 AND (c.ClassPeriodBegin = 'C' OR c.ClassPeriodEnd = 'C') AND e.StudentID = :stuid) as W3PC,
//     (SELECT e.ClassID FROM enrollment e, classes c WHERE c.ClassID = e.ClassID AND e.EnrollStatus = 'enrolled' AND c.ClassWeek = 4 AND (c.ClassPeriodBegin = 1 OR c.ClassPeriodEnd = 1) AND e.StudentID = :stuid) as W4P1,
//     (SELECT e.ClassID FROM enrollment e, classes c WHERE c.ClassID = e.ClassID AND e.EnrollStatus = 'enrolled' AND c.ClassWeek = 4 AND (c.ClassPeriodBegin = 2 OR c.ClassPeriodEnd = 2) AND e.StudentID = :stuid) as W4P2,
//     (SELECT e.ClassID FROM enrollment e, classes c WHERE c.ClassID = e.ClassID AND e.EnrollStatus = 'enrolled' AND c.ClassWeek = 4 AND (c.ClassPeriodBegin = 3 OR c.ClassPeriodEnd = 3) AND e.StudentID = :stuid) as W4P3,
//     (SELECT e.ClassID FROM enrollment e, classes c WHERE c.ClassID = e.ClassID AND e.EnrollStatus = 'enrolled' AND c.ClassWeek = 4 AND (c.ClassPeriodBegin = 4 OR c.ClassPeriodEnd = 4) AND e.StudentID = :stuid) as W4P4,
//     (SELECT e.ClassID FROM enrollment e, classes c WHERE c.ClassID = e.ClassID AND e.EnrollStatus = 'enrolled' AND c.ClassWeek = 4 AND (c.ClassPeriodBegin = 'A' OR c.ClassPeriodEnd = 'A') AND e.StudentID = :stuid) as W4PC,
//     (SELECT e.ClassID FROM enrollment e, classes c WHERE c.ClassID = e.ClassID AND e.EnrollStatus = 'enrolled' AND c.ClassWeek = 4 AND (c.ClassPeriodBegin = 'B' OR c.ClassPeriodEnd = 'B') AND e.StudentID = :stuid) as W4PB,
//     (SELECT e.ClassID FROM enrollment e, classes c WHERE c.ClassID = e.ClassID AND e.EnrollStatus = 'enrolled' AND c.ClassWeek = 4 AND (c.ClassPeriodBegin = 'C' OR c.ClassPeriodEnd = 'C') AND e.StudentID = :stuid) as W4PC");
//     $stmt->bindParam('stuid', $stu['StudentID']);
//     $stmt->execute();
//     $enrollData = $stmt->fetch(PDO::FETCH_ASSOC);

//     $famData[$aid]['EnrollInfo'] = $enrollData;

// }

$p['num_students'] = sizeof($famData);
$p['stu_json'] = json_encode($famData);

// Page info
echo UX::makeBreadcrumb(array(  'Staff Portal'      => '/staff/dashboard.php', 'Student List' => "/staff/manage/students.php"));
echo UX::grabPage('staff/manage/students', $p, true);

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

echo UX::grabPage('common/masthead_staff', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>

