<?php

/**
 * Course listing
 *
 * @author  Yectep Studios <info@yectep.hk>
 * @version 30122
 * @package Plume
 * @subpackage Staff
 */

header('Location: /staff/manage/family_view.php?fid='.$_REQUEST['fid'].'#/invoice');
exit();

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
    $_laoshi->perms(8,9,10,11,12);
}

// Get course information
$fam = FamStu::getFamilyById($_REQUEST['fid']);

$today = new DateTime();

$stmt = Data::prepare('SELECT e.*, cl.ClassWeek, s.StudentNameGiven, s.StudentNamePreferred, s.StudentNameLast, cl.ClassPeriodBegin, cl.ClassPeriodEnd, co.CourseSubj, co.CourseID, co.CourseTitle FROM enrollment e, classes cl, courses co, students s, families f WHERE f.FamilyID = :fid AND f.FamilyID = s.FamilyID AND s.StudentID = e.StudentID AND cl.ClassID = e.ClassID AND cl.CourseID = co.CourseID AND e.EnrollStatus = "enrolled" ORDER BY cl.ClassWeek ASC, cl.ClassPeriodBegin ASC');
$stmt->bindParam('fid', $_REQUEST['fid']);
$stmt->execute();

$invoice = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = Data::prepare('SELECT * FROM payments WHERE FamilyID = :fid');
$stmt->bindParam('fid', $_REQUEST['fid']);
$stmt->execute();

$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$p['billed_lines'] = '';
$total = 0;

foreach($invoice as $i => $line) {
    if (($line['CourseSubj'] == 'PHED') || ($line['CourseSubj'] == 'LANG') || ($line['CourseSubj'] == 'MSCT') || ($line['CourseSubj'] == 'ARTS')) {
        $amount = (($line['ClassPeriodBegin'] == $line['ClassPeriodEnd']) ? '800' : '1600');
    } else {
        $amount = '2500';
    }

    if ($line['ClassWeek'] == 2) $amount = floor($amount*0.8);

    $p['billed_line'] .= '<tr><td>'.date(DATE_SHORT, strtotime($line['EnrollCTS'])).'</td><td>'.strtoupper($line['CourseSubj']).str_pad($line['CourseID'], 3, '0', STR_PAD_LEFT).' - W'.$line['ClassWeek'].'P'.$line['ClassPeriodBegin'].' <em class="muted small">Enrollment for '.$line['StudentNamePreferred'].' '.$line['StudentNameLast'].'</td><td>'.Courses::getDueDate($line['ClassWeek']).'</td><td style="text-align:right;">$'.number_format($amount).'</td></tr>';
    $total += $amount;
}

$p['total_due'] = number_format($total);

foreach($payments as $i => $line) {
    $p['charge_lines'] .= '<tr><td>'.$line['PayMethod'].'</span></td><td>'.date(DATE_SHORT, strtotime($line['PayCTS'])).'</td><td>'.(($line['PayVerified'] == 0) ? '<a href="/staff/manage/verify_invoice.php?pid='.$line['PayID'].'" class="tipped" title="Click to verify this reported payment">'.$line['PayDesc'].'</a>' : $line['PayDesc'].' (<a href="javascript:;" onclick="dropPayment('.$line['PayID'].');">Delete this line</a>)').'</td><td style="text-align:right;">'.(($line['PayVerified'] == 1) ? '$'.number_format($line['PayAmount']) : '<em class="muted">Pending</em>').'</td></tr>';
    (($line['PayVerified'] == 1) ? $charges += $line['PayAmount'] : $charges = $charges);
}

$p['total_paid'] = number_format($charges);
$p['family_id'] = $_REQUEST['fid'];

$final_invoice = - $charges - $total;
$p['final_invoice'] = number_format((-1)*($final_invoice));

// Set default info
$h['title'] = 'Running Invoice | Family #'.$_REQUEST['fid'];
$n['management'] = 'active';
$n['my_name'] = $_laoshi->staff['StaffName'];

// Include header section
echo UX::makeHead($h, $n, 'common/header_staff', $_laoshi->fetchNavPage());

// Page info
echo UX::makeBreadcrumb(array(  'Staff Portal'      => '/staff/dashboard.php', 'Family Listing' => "/staff/manage/families.php", $fam['family']['FamilyName'] => "/staff/manage/family_view.php?fid=".$_REQUEST['fid'], 'Invoice' => "/staff/manage/invoices.php?fid=".$_REQUEST['fid']));
echo UX::grabPage('staff/manage/invoices', $p, true);

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

echo UX::grabPage('common/masthead_staff', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>

