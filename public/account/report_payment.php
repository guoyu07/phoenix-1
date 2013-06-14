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
$h['title'] = 'Report a Cheque Mailing';
$n['dashboard'] = 'active';
$n['my_name'] = $_fam->data['FamilyName'];
$p['family_id'] = $_fam->data['FamilyID'];
$p['today'] = date(DATE_FULL);
$p['family_name'] = $_fam->data['FamilyName'];

$stmt = Data::prepare('SELECT e.*, cl.ClassWeek, s.StudentNameGiven, s.StudentNamePreferred, s.StudentNameLast, cl.ClassPeriodBegin, cl.ClassPeriodEnd, co.CourseSubj, co.CourseID, co.CourseTitle FROM enrollment e, classes cl, courses co, students s, families f WHERE f.FamilyID = :fid AND f.FamilyID = s.FamilyID AND s.StudentID = e.StudentID AND cl.ClassID = e.ClassID AND cl.CourseID = co.CourseID AND e.EnrollStatus = "enrolled" ORDER BY cl.ClassWeek ASC, cl.ClassPeriodBegin ASC');
$stmt->bindParam('fid', $_fam->data['FamilyID']);
$stmt->execute();
$invoice = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = Data::prepare('SELECT * FROM payments WHERE FamilyID = :fid');
$stmt->bindParam('fid', $_fam->data['FamilyID']);
$stmt->execute();
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = 0;
$charges = 0;

$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach($invoice as $i => $line) {
    if (($line['CourseSubj'] == 'PHED') || ($line['CourseSubj'] == 'LANG') || ($line['CourseSubj'] == 'MSCT') || ($line['CourseSubj'] == 'ARTS')) {
        $amount = (($line['ClassPeriodBegin'] == $line['ClassPeriodEnd']) ? '800' : '1600');
    } else {
        $amount = '2500';
    }

    if ($line['ClassWeek'] == 2) $amount = floor($amount*0.8);
    $total += $amount;
}

foreach($payments as $i => $line) {
    (($line['PayVerified'] == 1) ? $charges += $line['PayAmount'] : $charges = $charges);
}

$final_invoice = - $charges - $total;

if ($final_invoice < 0) {
    $p['message'] = '<div class="alert alert-red"><span class="featured">Account Balance: <strong>HK$'.number_format(abs($final_invoice)).'</strong></span></div>';
} else {
    $p['message'] = '<div class="alert alert-green"><span class="featured">You have no outstanding balance. Thank you!</div>';;
}


// Include header section
echo UX::makeHead($h, $n, 'common/header_public', 'common/nav_account');

// Page info
echo UX::makeBreadcrumb(array(  'My Family' => "/account/dashboard.php", 'My Invoice &amp; Account' => "/account/invoice.php", 'Report a Payment' => "javascript:;"));
echo UX::grabPage('account/report_payment', $p, true);

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

echo UX::grabPage('common/masthead', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>

