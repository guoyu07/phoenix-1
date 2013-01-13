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
define('PHX_NEWS',      true);
define('PHX_UX',        true);


// Include common ignition class
require_once(PTP . 'php/ignition.php');

// If a request has been submitted, try perfomring a login
if (isset($_POST['email']) && isset($_POST['pass'])) {
    
    // Does email address exist in the database?
    if (!ACL::checkEmail($_POST['email'])) {
        Common::logAction('http.post.login', 'failed', 'EMAIL='.$_POST['email'], 'inexistent account');
        header('Location: ./login.php?msg=error_email');
        exit();
    }
    
    // Check password
    $login_check = ACL::checkLogin($_POST['email'], $_POST['pass']);
    
    if ($login_check === false) {
        header('Location: ./login.php?msg=error_pass');
        exit();
    } elseif ($login_check == 0) {
        // Account is inactive
        header('Location: ./login.php?msg=error_inactive');
        exit();
    }
    
    // Everything's good, set session info and we're good to transfer to dashboard
    ACL::generateSession($login_check);
    header('Location: ./dashboard.php');
    
}

// Set page switch variables
$h['title'] = 'Sign In';
$n['signin'] = 'active';

// Error page
if (array_key_exists('msg', $_GET)) {
	switch ($_GET['msg']) {
		case 'registered':
			$error = '<div class="alert alert-green"><img src="/assets/icons/tick.png" title="[OK]" /> Thank you for registering. We just sent you a link to the email address you specified. You need to click that link to activate your account.</div>';
		break;
		case 'activated':
			$error = '<div class="alert alert-green"><img src="/assets/icons/tick.png" title="[OK]" /> Thank you for activating your account. You may now sign in.</div>';
		break;
		case 'error_email':
			$error = '<div class="alert alert-red"><strong>Whoops!</strong> The email address you used doesn\'t exist. Have you made an account yet?</div>';
		break;
		case 'error_pass':
			$error = '<div class="alert alert-red">The password you entered doesn\'t match the one we have on file. Please try again. If you forgot your password, use the Forgot Password link.</div>';
		break;
		case 'error_inactive':
			$error = '<div class="alert alert-red">Your account is not yet active. To activate your account, please refer to the original welcome email we sent to your account when you registered.</div>';
		break;
		default:
			$error = '';
		break;
	}
} else {
	$error = '';
}


// Include header section
echo UX::makeHead($h, $n);

// Page info
echo UX::makeBreadcrumb(array(	'Sign In'		=> '/account/login.php'));
echo UX::grabPage('account/login', array('error' => $error), true);

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

echo UX::grabPage('common/masthead', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>