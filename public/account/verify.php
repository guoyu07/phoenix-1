<?php

/**
 * CSS style definition guide as POC
 *
 * @author	Yectep Studios <info@yectep.hk>
 * @version	20903
 * @package Plume
 */


define('PTP',   '../../private/');
define('PHX_SCRIPT_TYPE',   'HTML');


// Include common ignition class
require_once(PTP . 'php/ignition.php');

// No required items?
if (!isset($_GET['email']) || !isset($_GET['v'])) {
    header('Location: ./login.php');
    exit();
}

// No such email?
$objid = ACL::checkSsoEmail($_GET['email']);
if ($objid) {
    $obj = ACL::getSsoObject($objid);

    if (sha1($obj['ObjHash']) == $_GET['v']) {
        try {
            $stmt = Data::prepare('UPDATE `families` SET `FamilyAccountStatus` = 1 WHERE `ObjID` = :objid AND `FamilyAccountStatus` = 0');
            $stmt->bindParam('objid', $objid, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            Common::throwNiceDataException($e);
        }
        
        header('Location: ./login.php?msg=activated');
        exit();
    }
} else {
    header('Location: ./login.php?msg=error_email');
    exit();
}

?>