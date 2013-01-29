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

    switch($_REQUEST['method']) {
    	case 'archive_application':
    		$_laoshi->perms(6, 7, 8);
    		$stmt = Data::prepare('UPDATE `applications` SET `AppStatus` = "archived" WHERE `AppID` = :appid LIMIT 1');
    		$stmt->bindParam('appid', $_REQUEST['data'], PDO::PARAM_INT);
    		$stmt->execute();

    		$result['status'] = 'success';
    		$result['code'] = 2000;
    		$result['msg'] = '[OK] '.$stmt->rowCount().' rows affected.';
    	break;
        case 'create_course':
            $_laoshi->perms(6, 7);
            // if (Courses::addCourse())
        break;
    	default:
			$result['status'] = 'failure';
			$result['code'] = 2404;
			$result['msg'] = 'Invalid method';
		break;
    }
}

// Send out json data
echo json_encode($result);
exit();

?>

