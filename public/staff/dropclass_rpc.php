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
define('PHX_COURSES',   true);
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

    if (!$_GET['eid'] || !$_GET['sid']) {
        $result['status'] = 'failure';
        $result['code'] = 2400;
        $result['msg'] = 'No EID class string provided';
    } else {
        // Drop the kid!
        $stmt = Data::prepare('UPDATE `enrollment` SET EnrollStatus = "dropped" WHERE StudentID = :sid AND EnrollID = :eid');
        $stmt->bindParam('eid', $_GET['eid'], PDO::PARAM_INT);
        $stmt->bindParam('sid', $_GET['sid'], PDO::PARAM_INT);
        $stmt->execute();

        $result['status'] = 'success';

    }
}

// Send out json data
echo json_encode($result);
exit();

?>

