<?php

/**
 * Regular logout page
 *
 * @author  Yectep Studios <info@yectep.hk>
 * @version 30113
 * @package Plume
 */


define('PTP',   '../../private/');
define('PHX_SCRIPT_TYPE', 'plain');


// Include common ignition class
require_once(PTP . 'php/ignition.php');

// We require a staff login for this page
if (!ACL::checkLogin('public')) {
    header('Location: ./login.php?msg=error_nologin');
    exit();
} else {
    ACL::logout();
    header('Location: ./login.php?msg=logout');
    exit();
}

?>

