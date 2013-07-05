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
define('PHX_SCRIPT_TYPE',   'JSON');
define('PHX_COURSES', 	true);
define('PHX_MAILER',    true);
define('PHX_LAOSHI',    true);


// Include common ignition class
require_once(PTP . 'php/ignition.php');

// We require a staff login for this page
if (!ACL::checkLogin('staff')) {
    $result['status'] = 'failure';
    $result['code'] = 2400;
    $result['msg'] = 'Login not provided';
} else {
    $_laoshi = new Laoshi($_SESSION['SSOID']);

    $_laoshi->perms(8,9,10,11,12);
    try {
        $stmt = Data::prepare('UPDATE `families` SET FamilyIC = :ft WHERE FamilyID = :fid LIMIT 1');
        $stmt->bindParam('fid', $_REQUEST['fid'], PDO::PARAM_INT);
        $stmt->bindParam('ft', $_REQUEST['ic'], PDO::PARAM_STR);
        $stmt->execute();
        $result['status'] = 'success';
        $result['code'] = 2000;
        $result['msg'] = '[OK] Charge successfully added to invoice.';
        $result['timestr'] = date('g:ia');
    } catch (PDOException $e) {
        $result['stauts'] = 'failure';
        $result['code'] = 2500;
        $result['msg'] = '[SQL] Error: '.$e->getMessage();
    }
}

// Send out json data
echo json_encode($result);
exit();

?>

