<?php

/**
 * Ignition script to initiate required classes. For sake of simplicity, we require
 * classes based on whether or not certain constants are defined.
 *
 * @author	Yectep Studios <info@yectep.hk>
 * @version	20704
 * @package Phoenix
 */

// Start time tracking
$t['start'] = microtime(true);

// Define universal standards
define('DATETIME_FULL',		'l, j F Y g:i:sa');
define('DATETIME_SHORT',	'd/m/y H:i:s');
define('DATE_FULL',			'j F Y');
define('DATE_SHORT',		'd/m/y');

// Define environmental constants
define('REGISTRATION_OPEN', true);

// Require basic classes
require_once('class.common.php');
require_once('class.security.php');
require_once('class.data.php');
require_once('class.acl.php');
require_once('class.browser.php');

// Use selectors to initiate requires
// DERPALERT: These are in alphabetical order except for UX, which kinda has to come first.
if (defined('PHX_UX')) require_once('class.ux.php');
if (defined('PHX_COURSES')) require_once('class.courses.php');
if (defined('PHX_ENROL')) require_once('class.enrolment.php');
if (defined('PHX_MAILER')) require_once('class.mailer.php');
if (defined('PHX_STUDENT')) require_once('class.student.php');
if (defined('PHX_STAFF')) require_once('class.staff.php');


// Everybody needs the common class
$_PHX = new Common(PHX_SCRIPT_TYPE);
$_BD = new Browser();

// Oh and...definitely no IE
if (isset($_GET['forceie']) && ($_GET['forceie'] == 1)) {
    setcookie('OverrideIE', 1, time()+3600*24*30, '/');
} else if (!isset($_COOKIE['OverrideIE']) && (PHX_SCRIPT_TYPE == 'HTML') && ($_BD->getBrowser() == Browser::BROWSER_IE) && ($_BD->getVersion() <= 7)) {
    // IE. DIE.
    echo UX::grabPage('dev/ie', array('ua' => $_SERVER['HTTP_USER_AGENT']), true);
    exit();
}

?>