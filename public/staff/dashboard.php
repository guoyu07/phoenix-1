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
define('PHX_SCRIPT_TYPE',   'plain');
define('PHX_UX',        true);


// Include common ignition class
require_once(PTP . 'php/ignition.php');

if (!ACL::checkLogin()) {
    header('Location: ./index.php?msg=error_nologin');
    exit();
}

// We require login
echo "LOGIN INFORMATION ==========\n";
echo "SSOID=".$_SESSION['SSOID']."\n";
echo "Verification=".$_SESSION['AuthCheck']."\n";
echo "CheckLogin(): ".ACL::checkLogin();

?>