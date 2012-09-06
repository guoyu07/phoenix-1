<?php


/**
 * Mandrill connectivity email test page
 *
 * @author	Yectep Studios <info@yectep.hk>
 * @version	20813
 * @package Plume
 */


define('PTP',   '../../private/');
define('PHX_SCRIPT_TYPE',   'HTML');
define('PHX_MAILER',      true);

// Include common ignition class
require_once(PTP . 'php/ignition.php');

if (method_exists('Mailer', 'send')) {
    Mailer::send(array('email' => 'yectep@gmail.com', 'name' => 'Chester Li'), 'This is a test', 'This is just a test message to see whether the Mandrill-based mailer class works.'."\n\n".'Also testing HTML emails...');
} else {
    Common::niceException('The Mailer::send() method was not imported correctly and therefore the email could not be sent.');
}

?>