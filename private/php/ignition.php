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

// Require basic classes
require_once('class.common.php');
require_once('class.security.php');
require_once('class.data.php');
require_once('class.acl.php');

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

?>