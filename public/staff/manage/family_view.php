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
$p['family_email'] = $fam['family']['FamilyEmail'];
$p['family_cts'] = date(DATETIME_FULL, strtotime($fam['family']['FamilyCTS']));
$p['family_address'] = $fam['family']['FamilyAddress'];
$p['family_llts'] = date(DATETIME_FULL, strtotime($fam['family']['FamilyLLTS']));
$p['family_lats'] = date(DATETIME_FULL, strtotime($fam['family']['FamilyLATS']));

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
    $s['emer_relation'] = $student['StudentECRelation'];
    $s['emer_phone'] = $student['StudentECPhone'];
    $s['med_meds'] = $student['StudentMedMedications'];
    $s['med_cond'] = $student['StudentMedCondition'];
    $s['cos'] = (($student['StudentCOS'] == 1) ? '<img src="/assets/icons/exclamation.png" /> Student is child of staff' : '<em class="muted">COS status not indicated</em>');
    $p['children'] .= UX::grabPage('staff/manage/child_list_snippet', $s, true);
}

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

