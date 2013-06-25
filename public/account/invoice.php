<?php

/**
 * Family dashboard
 *
 * @author  Yectep Studios <info@yectep.hk>
 * @version 30104
 * @package Plume
 */


define('PTP',   '../../private/');
define('PHX_SCRIPT_TYPE',   'HTML');
define('PHX_UX',        true);
define('PHX_STUDENT',   true);
define('PHX_COURSES',   true);
define('PHX_FINANCES',  true);


// Include common ignition class
require_once(PTP . 'php/ignition.php');

// We require a staff login for this page
if (!ACL::checkLogin('public')) {
    header('Location: ./login.php?msg=error_nologin');
    exit();
} else {
    $_fam = new FamStu('family', $_SESSION['SSOID']);
}

// Triage and get default staff page
$h['title'] = 'My Account and Invoice';
$n['dashboard'] = 'active';
$n['my_name'] = $_fam->data['FamilyName'];

$stmt = Data::prepare('SELECT e.*, cl.ClassWeek, s.StudentNameGiven, s.StudentNamePreferred, s.StudentNameLast, cl.ClassPeriodBegin, cl.ClassPeriodEnd, co.CourseSubj, co.CourseID, co.CourseTitle FROM enrollment e, classes cl, courses co, students s, families f WHERE f.FamilyID = :fid AND f.FamilyID = s.FamilyID AND s.StudentID = e.StudentID AND cl.ClassID = e.ClassID AND cl.CourseID = co.CourseID AND e.EnrollStatus = "enrolled" ORDER BY cl.ClassWeek ASC, cl.ClassPeriodBegin ASC');
$stmt->bindParam('fid', $_fam->data['FamilyID']);
$stmt->execute();
$invoice = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = Data::prepare('SELECT * FROM payments WHERE FamilyID = :fid');
$stmt->bindParam('fid', $_fam->data['FamilyID']);
$stmt->execute();

$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$p['billed_lines'] = '';
$p['charge_lines'] = '';
$p['pay_timestamp'] = sha1(time().":gibberish!");
$total = 0;
$charges = 0;

foreach($invoice as $i => $line) {
    if (($line['CourseSubj'] == 'PHED') || ($line['CourseSubj'] == 'LANG') || ($line['CourseSubj'] == 'MSCT') || ($line['CourseSubj'] == 'ARTS')) {
        $amount = (($line['ClassPeriodBegin'] == $line['ClassPeriodEnd']) ? '800' : '1600');
    } else {
        $amount = 2500;
    }

    if ($line['ClassWeek'] == 2) $amount = floor($amount*0.8);

    $p['billed_lines'] .= '<tr><td>'.($i+1).'</td><td><span class="tipped" title="This is the date on which you enrolled your child">'.date(DATE_SHORT, strtotime($line['EnrollCTS'])).'</span></td><td>'.strtoupper($line['CourseSubj']).str_pad($line['CourseID'], 3, '0', STR_PAD_LEFT).' - W'.$line['ClassWeek'].'P'.$line['ClassPeriodBegin'].' <em class="muted small">Enrollment for '.$line['StudentNamePreferred'].' '.$line['StudentNameLast'].'</td><td>'.Courses::getDueDate($line['ClassWeek']).'</td><td style="text-align:right;">$'.number_format($amount).'</td></tr>';
    $total += $amount;
}

$p['total_due'] = number_format($total);

foreach($payments as $i => $line) {
    $p['charge_lines'] .= '<tr><td>'.$line['PayMethod'].'</span></td><td><span class="tipped" title="'.date(DATETIME_FULL, strtotime($line['PayCTS'])).'">'.date(DATE_SHORT, strtotime($line['PayCTS'])).'</span></td><td>'.$line['PayDesc'].'</td><td style="text-align:right;">'.(($line['PayVerified'] == 1) ? '$'.number_format($line['PayAmount']) : '<em class="muted">Pending</em>').'</td></tr>';
    (($line['PayVerified'] == 1) ? $charges += $line['PayAmount'] : $charges = $charges);
}

$p['total_paid'] = number_format($charges);

$final_invoice = - $charges - $total;

if ($final_invoice < 0) {
    $p['message'] = '<div class="alert alert-red"><span class="featured">Account Balance: <strong>HK$'.number_format(abs($final_invoice)).'</strong></span></div>';
} else {
    $p['message'] = '<div class="alert alert-green"><span class="featured">You have no outstanding balance. Thank you!</div>';;
}


// Include header section
echo UX::makeHead($h, $n, 'common/header_public', 'common/nav_account');

// Page info
echo UX::makeBreadcrumb(array(  'My Family' => "/account/dashboard.php", 'My Invoice &amp; Account' => "/account/invoice.php"));
echo UX::grabPage('account/invoice', $p, true);

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

echo UX::grabPage('common/masthead', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>

