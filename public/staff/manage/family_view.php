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
$p['children'] = '';
$p['family_name'] = $fam['family']['FamilyName'];
$p['family_id'] = $fam['family']['FamilyID'];
$p['family_email'] = $fam['family']['FamilyEmail'];
$p['family_hphone'] = ((strlen($fam['family']['FamilyPhoneHome']) == 8) ? '<span class="muted">+852</span> '.substr($fam['family']['FamilyPhoneHome'], 0, 4).'-'.substr($fam['family']['FamilyPhoneHome'],4,4) : $fam['family']['FamilyPhoneHome'].' <em class="muted" style="float:right;">(International?)</em>');
$p['family_mphone'] = ((strlen($fam['family']['FamilyPhoneMobile']) == 8) ? '<span class="muted">+852</span> '.substr($fam['family']['FamilyPhoneMobile'], 0, 4).'-'.substr($fam['family']['FamilyPhoneMobile'],4,4) : $fam['family']['FamilyPhoneMobile'].' <em class="muted" style="float:right;">(International?)</em>');
$p['family_cts'] = date(DATETIME_FULL, strtotime($fam['family']['FamilyCTS']));
$p['family_address'] = $fam['family']['FamilyAddress'];
$p['family_llts'] = date(DATETIME_FULL, strtotime($fam['family']['FamilyLLTS'])+28800);
$p['family_lats'] = date(DATETIME_FULL, strtotime($fam['family']['FamilyLATS'])+28800);
$p['family_comments'] = $fam['family']['FamilyIC'];

$today = new DateTime();

foreach($fam['children'] as $student) {
    $dobDo = new DateTime($student['StudentDOB']);
    $s['preferred_name'] = $student['StudentNamePreferred'];
    $s['last_name'] = $student['StudentNameLast'];
    $s['given_name'] = $student['StudentNameGiven'];
    $s['dob'] = date(DATE_FULL, strtotime($student['StudentDOB']));
    $s['age'] = $dobDo->diff($today)->y;
    $s['school'] = $student['StudentSchool'];
    $s['created_date'] = date(DATETIME_FULL, strtotime($student['StudentCTS']));
    $s['submitted_date'] = (($student['StudentSubmitted'] == 1) ? '<img src="/assets/icons/tick.png" /> Schedule has been submitted' : '<em class="muted">Schedule not submitted</em>');
    $s['emer_name'] = $student['StudentECName'];
    $s['sid'] = $student['StudentID'];
    $s['emer_relation'] = $student['StudentECRelation'];
    $s['emer_phone'] = $student['StudentECPhone'];
    $s['med_meds'] = $student['StudentMedMedications'];
    $s['med_cond'] = $student['StudentMedCondition'];
    $s['cos'] = (($student['StudentCOS'] == 1) ? '<img src="/assets/icons/exclamation.png" /> Student is child of staff' : '<em class="muted">COS status not indicated</em>');
    $p['children'] .= UX::grabPage('staff/manage/child_list_snippet', $s, true);
}


// From invoices
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
    $p['charge_lines'] .= '<tr><td>'.$line['PayMethod'].'</span></td><td>'.date(DATE_SHORT, strtotime($line['PayCTS'])).'</td><td>'.(($line['PayVerified'] == 0) ? '<a href="/staff/manage/verify_invoice.php?pid='.$line['PayID'].'" class="tipped" title="Click to verify this reported payment">'.$line['PayDesc'].'</a>' : $line['PayDesc']).' <span style="float:right;" class="hovermagic"><a href="javascript:;" onclick="dropPayment('.$line['PayID'].');" class="tipped" title="Click to delete this line"><img src="/assets/icons/cross.png" /></a> '.(($line['PayFlag'] == 0) ? '<a href="javascript:;" onclick="toggleFlag('.$line['PayID'].', '.$line['PayFlag'].');" class="tipped" title="Cheque is in the office. Click to mark sent to Betty."><img src="/assets/icons/mail.png"></a>' : '<a href="javascript:;" onclick="toggleFlag('.$line['PayID'].', '.$line['PayFlag'].');" class="tipped" title="Cheque sent to Betty. Click to mark in office."><img src="/assets/icons/mail-send.png"></a>').'</span>'.'</td><td style="text-align:right;">'.(($line['PayVerified'] == 1) ? '$'.number_format($line['PayAmount']) : '<em class="muted">Pending</em>').'</td></tr>';
    (($line['PayVerified'] == 1) ? $charges += $line['PayAmount'] : $charges = $charges);
}

$p['total_paid'] = number_format($charges);
$p['family_id'] = $_REQUEST['fid'];

$final_invoice = - $charges - $total;
$p['final_invoice'] = number_format((-1)*($final_invoice));

// Set default info
$h['title'] = 'Profile | Family #'.$_REQUEST['fid'];
$n['management'] = 'active';
$n['my_name'] = $_laoshi->staff['StaffName'];

// Include header section
echo UX::makeHead($h, $n, 'common/header_staff', $_laoshi->fetchNavPage());

// Page info
echo UX::makeBreadcrumb(array(  'Staff Portal'      => '/staff/dashboard.php', 'Family Listing' => "/staff/manage/families.php", $fam['family']['FamilyName'] => "/staff/manage/family_view.php?fid=".$_REQUEST['fid']));
echo UX::grabPage('staff/manage/family_view', $p, true);

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

echo UX::grabPage('common/masthead_staff', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>

