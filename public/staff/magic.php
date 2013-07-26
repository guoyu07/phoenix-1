<?php

/**
 * Staff dashboard
 *
 * @author  Yectep Studios <info@yectep.hk>
 * @version 30104
 * @package Plume
 * @subpackage Staff
 */


define('PTP',   '../../private/');
define('PHX_SCRIPT_TYPE',   'HTML');
define('PHX_UX',        true);
define('PHX_LAOSHI',    true);


// Include common ignition class
require_once(PTP . 'php/ignition.php');

// We require a staff login for this page
if (!ACL::checkLogin('staff')) {
    header('Location: ./index.php?msg=error_nologin');
    exit();
} else {
    $_laoshi = new Laoshi($_SESSION['SSOID']);
}

// Triage and get default staff page
$h['title'] = 'Magic Number';
$n['my_name'] = $_laoshi->staff['StaffName'];

$stmt = Data::query("select sum(PayAmount) as total from payments where PayVerified = 1 and PayAmount < 0 and PayMethod = 'Cheque'");
$total = $stmt->fetch(PDO::FETCH_ASSOC);
$total_real = $total['total']*(-1);

$p['total'] = number_format($total_real);


// Include header section
echo UX::makeHead($h, $n, 'common/header_staff', $_laoshi->fetchNavPage());

// Page info
echo UX::makeBreadcrumb(array(  'Staff Portal'      => '/staff/index.php', 'My Dashboard' => "/staff/dashboard.php"));
echo UX::grabPage('staff/magic', $p, true);

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

echo UX::grabPage('common/masthead_staff', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>

