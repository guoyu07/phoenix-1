<?php

/**
 * Staff signin page
 *
 * @author	Yectep Studios <info@yectep.hk>
 * @version	20707
 * @package Plume
 * @subpackage Staff
 */


define('PTP',   '../../private/');
define('PHX_SCRIPT_TYPE',   'HTML');
define('PHX_UX',        true);


// Include common ignition class
require_once(PTP . 'php/ignition.php');

// If a request has been submitted, try perfomring a login
if (isset($_POST['email']) && isset($_POST['pass'])) {
    
    // Perform a login
    $login = ACL::login($_POST['email'], $_POST['pass'], 'staff');
    
    if ($login === null) {
        Common::logAction('http.staff.login', 'failure', 'E='.$_POST['email'], 'No matching portal email');
        header('Location: ./index.php?msg=error_email');
        exit();
    } elseif ($login === false) {
        Common::logAction('http.staff.login', 'failure', 'E='.$_POST['email'], 'Invalid password match');
        header('Location: ./index.php?msg=error_pass');
        exit();
    } else {
        Common::logAction('http.staff.login', 'success', 'SSOID='.$login);
    }
    
    // Everything's good, set session info and we're good to transfer to dashboard
    ACL::genSession($login);
    header('Location: ./dashboard.php');
    exit();
}

// Set page switch variables
$h['title'] = 'Sign In';
$n['signin'] = 'active';

// Error page
if (array_key_exists('msg', $_GET)) {
	switch ($_GET['msg']) {
		case 'logout':
			$error = '<div class="alert alert-green"><img src="/assets/icons/tick.png" title="[OK]" /> You have been successfully logged out. See you next time!</div>';
		break;
		case 'error_email':
			$error = '<div class="alert alert-red"><strong>Whoops!</strong> The email address you used doesn\'t exist. Please remember this login is for staff only.</div>';
		break;
		case 'error_pass':
			$error = '<div class="alert alert-red"><img src="/assets/icons/cross.png" title="[!]" /> The password you entered doesn\'t match the one we have on file. Please try again. If you forgot your password, use the Forgot Password link.</div>';
		break;
		case 'error_nologin':
			$error = '<div class="alert alert-red">Please login to access this resource.</div>';
		break;
		default:
			$error = '<div class="alert alert-yellow">This computer system is restricted to authorized users only. All access attempts are logged and unauthorized accesses are strictly forbidden.</div>';
		break;
	}
} else {
	$error = '<div class="alert">This computer system is restricted to authorized users only. All access attempts are logged and unauthorized accesses are strictly forbidden.</div>';
}


// Include header section
echo UX::makeHead($h, $n, 'common/header_staff', 'common/nav_public_staff');

// Page info
echo UX::makeBreadcrumb(array(	'Staff Portal'   => '/staff',   'Sign In'		=> '/staff/index.php'));
echo UX::grabPage('staff/login', array('error' => $error), true);

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

echo UX::grabPage('common/masthead_staff', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>