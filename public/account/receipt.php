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

$stmt = Data::prepare('SELECT * FROM `payments` WHERE `PayID` = :pid');
$stmt->bindParam('pid', $_GET['pid'], PDO::PARAM_INT);
$stmt->execute();
$famcheck = $stmt->fetch(PDO::FETCH_ASSOC);

if (sha1($_GET['pid'].':'.$famcheck['FamilyID'].':cis_summer')) {
    $url = 'http://summer.cis.edu.hk/staff/scanman.php?pid=7&m=payment';
    $p['img'] = 'https://chart.googleapis.com/chart?chs=192x192&cht=qr&chl='.urlencode($url);
    $p['family_id'] = $_fam->data['FamilyID'];
    $p['date'] = date(DATETIME_FULL, strtotime($famcheck['PayCTS']));
    $p['desc'] = $famcheck['PayDesc'];
    $p['id'] = crc32($_GET['c']);
    echo UX::grabPage('public/receipt', $p, true);
} else {
    echo 'Invalid!';
}

?>

