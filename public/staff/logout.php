<?php

/**
 * Staff logout page
 *
 * @author  Yectep Studios <info@yectep.hk>
 * @version 30113
 * @package Plume
 * @subpackage Staff
 */


define('PTP',   '../../private/');
define('PHX_SCRIPT_TYPE', 'plain');


// Include common ignition class
require_once(PTP . 'php/ignition.php');

// We require a staff login for this page
if (!ACL::checkLogin('staff')) {
    header('Location: ./index.php?msg=error_nologin');
    exit();
} else {
    ACL::logout();
    header('Location: ./index.php?msg=logout');
    exit();
}

?>

