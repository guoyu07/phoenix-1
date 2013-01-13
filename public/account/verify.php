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
if (ACL::checkEmail($_GET['email'])) {
    $famInfo = ACL::getUserData($_GET['email']);
    if (sha1($famInfo['password']) == $_GET['v']) {
        try {
            $stmt = Data::prepare('UPDATE `families` SET `FamilyAccountStatus` = 1 WHERE `FamilyEmail` = :email AND `FamilyAccountStatus` = 0');
            $stmt->bindParam('email', $famInfo['email']);
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