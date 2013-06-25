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
define('PHX_MAILER',    true);


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

// Grab payment ID
$stmt = Data::prepare('SELECT * FROM `payments` WHERE `PayID` = :pid LIMIT 1');
$stmt->bindParam('pid', $_REQUEST['pid']);
$stmt->execute();
$paydata = $stmt->fetch(PDO::FETCH_ASSOC);


// Get family information
$fam = FamStu::getFamilyById($paydata['FamilyID']);

// P array
$p['family_id'] = $fam['family']['FamilyID'];
$p['payment_id'] = $paydata['PayID'];
$p['desc'] = $paydata['PayDesc'];
$p['method'] = $paydata['PayMethod'];
$p['amount'] = (-1)*$paydata['PayAmount'];
$p['payts'] = date(DATETIME_FULL, strtotime($paydata['PayCTS']));

// What are we doing?
if (isset($_POST['method'])) {
    $amount = (-1)*$_POST['amount'];
    $stmt = Data::prepare('UPDATE `payments` SET PayDesc = :desc, PayVerified = 1, PayMethod = :method, PayAmount = :amount WHERE PayID = :pid LIMIT 1');
    $stmt->bindParam('desc', $_POST['desc'], PDO::PARAM_STR);
    $stmt->bindParam('method', $_POST['method'], PDO::PARAM_STR);
    $stmt->bindParam('amount', $amount, PDO::PARAM_INT);
    $stmt->bindParam('pid', $_POST['pid'], PDO::PARAM_INT);
    $stmt->execute();

    $e['payment_id'] = $paydata['PayID'];
    $e['desc'] = $paydata['PayDesc'];
    $e['method'] = $paydata['PayMethod'];
    $e['amount_formatted'] = 'HK$'.number_format((-1)*$paydata['PayAmount']);
    $e['cts'] = date(DATETIME_FULL, strtotime($paydata['PayCTS']));
    $e['rts'] = date(DATETIME_FULL);

    Mailer::send(array('name' => $fam['family']['FamilyName'], 'email' => $fam['family']['FamilyEmail']), '[CIS Summer] Payment Confirmation', UX::grabPage('text_snippets/email_receipt', $e, true));

    // Set default info
    $h['title'] = 'Payment Verified!';
    $n['management'] = 'active';
    $n['my_name'] = $_laoshi->staff['StaffName'];

    // Include header section
    echo UX::makeHead($h, $n, 'common/header_staff', $_laoshi->fetchNavPage());

    // Page info
    echo UX::makeBreadcrumb(array(  'Staff Portal'      => '/staff/dashboard.php', 'Family Listing' => "/staff/manage/families.php", $fam['family']['FamilyName'] => "/staff/manage/family_view.php?fid=".$fam['family']['FamilyID'], 'Invoice' => "/staff/manage/invoices.php?fid=".$fam['family']['FamilyID'], 'Payment Verified' => "/staff/manage/verify_invoice.php?pid=".$_REQUEST['pid']));
    echo UX::grabPage('staff/manage/verified', $p, true);

    // Before footer grab time spent
    $t['end'] = microtime(true);
    $time = round(($t['end'] - $t['start']), 3);

    echo UX::grabPage('common/masthead_staff', array('time' => $time), true);
    echo UX::grabPage('common/footer', null, true);

} else {


    // Set default info
    $h['title'] = 'Verify Payment | Family #'.$fam['family']['FamilyID'];
    $n['management'] = 'active';
    $n['my_name'] = $_laoshi->staff['StaffName'];

    // Include header section
    echo UX::makeHead($h, $n, 'common/header_staff', $_laoshi->fetchNavPage());

    // Page info
    echo UX::makeBreadcrumb(array(  'Staff Portal'      => '/staff/dashboard.php', 'Family Listing' => "/staff/manage/families.php", $fam['family']['FamilyName'] => "/staff/manage/family_view.php?fid=".$fam['family']['FamilyID'], 'Invoice' => "/staff/manage/invoices.php?fid=".$fam['family']['FamilyID'], 'Verify Payment' => "/staff/manage/verify_invoice.php?pid=".$_REQUEST['pid']));
    echo UX::grabPage('staff/manage/verify_invoice', $p, true);

    // Before footer grab time spent
    $t['end'] = microtime(true);
    $time = round(($t['end'] - $t['start']), 3);

    echo UX::grabPage('common/masthead_staff', array('time' => $time), true);
    echo UX::grabPage('common/footer', null, true);

}

?>

