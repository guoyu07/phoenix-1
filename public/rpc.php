<?php

/**
 * CSS style definition guide as POC
 *
 * @author	Yectep Studios <info@yectep.hk>
 * @version	20707
 * @package Plume
 */


define('PTP',   '../private/');
define('BETA',  true);
define('PHX_SCRIPT_TYPE',   'JSON');


// Include common ignition class
require_once(PTP . 'php/ignition.php');

// If there's no security key we generate one
$skip = false;
if (!isset($_REQUEST['uid']) || !isset($_REQUEST['key'])) {
    $output["uid"] = uniqid();
    $output["key"] = sha1(session_id().$output["uid"].$_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT']);
} else {
    // Check security key
    if (isset($_REQUEST['key']) && isset($_REQUEST['uid']) && (sha1(session_id().$_REQUEST['uid'].$_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT']) == $_REQUEST['key'])) {
        // Alright
        $skip = false;
    } else {
        // Failed validation
        $output["responseCode"] = 2403;
        $output["error"] = "Request key failed to validate. Please reinitiate request.";
        $skip = true;
    }
}

if ($skip) die(json_encode($output));


if (isset($_REQUEST['method'])) {

    switch($_REQUEST['method']) {
        case 'checkEmail':
            if (isset($_REQUEST['data'])) {
                $output["responseCode"] = 2200;
                if (Security::checkEmail($_REQUEST['data'])) $output["response"] = true;
                else $output["response"] = false;
            } else {
                $output["responseCode"] = 2400;
                $output["error"] = "Missing data element (email).";
            }
            break;
        default:
            $output["responseCode"] = 2404;
            $output["error"] = "The method specified does not exist or has been deprecated.";
        break;
    }

} else {

    $output["responseCode"] = 2401;
    $output["error"] = "No method was specified in the request";
    
}

die(json_encode($output));
?>