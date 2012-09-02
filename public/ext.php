<?php

/**
 * URL redirector for new webpage
 *
 * @author	Yectep Studios <info@yectep.hk>
 * @version	20827
 * @package Plume
 */


define('PTP',   '../private/');
define('PHX_SCRIPT_TYPE',   'HTML');
define('PHX_NEWS',      true);
define('PHX_UX',        true);


// Include common ignition class
require_once(PTP . 'php/ignition.php');

// Log it
Common::logAction('http.get.url-redirect', 'success', null, 'dest='.urldecode($_GET['url']));

// Redirect
header('Location: '.urldecode($_GET['url']));
exit();

?>