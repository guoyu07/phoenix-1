<?php

/**
 * Default landing splash/info page.
 *
 * @author	Yectep Studios <info@yectep.hk>
 * @version	30112
 * @package Plume
 */

define('PTP',   '../private/');
define('PHX_SCRIPT_TYPE',   'HTML');
define('PHX_MAILER',    true);
define('PHX_UX',        true);


// Include common ignition class
require_once(PTP . 'php/ignition.php');

// Set page switch variables
$h['title'] = 'Welcome';
$n['feedback'] = 'active';

if (isset($_SERVER['QUERY_STRING']) && ($_SERVER['QUERY_STRING'] == 'submit')) {
	$b['blurb'] = '<div class="alert alert-green"><img src="/assets/icons/tick.png" alt="[OK]" /> Thank you for submitting feedback on our website/program. We take all comments to heart and hope to improve in all possible ways. If you have requested a response, we will get back to you as soon as possible! <strong><a href="/">Return home</a></strong></div>';
	$e['timestamp'] = date(DATETIME_FULL);
	$e['name'] = $_POST['feedback_name'];
	$e['topic'] = $_POST['feedback_topic'];
	$e['comments'] = $_POST['feedback_comments'];
	$e['response'] = (($_POST['feedback_reply'] == "1") ? 'A reply to the customer is requested. Use the email given above. DO NOT FORWARD THIS EMAIL - instead, copy the comments directly into a new email.' : 'No reply is requested.');
	$e['email'] = $_POST['feedback_email'];
	$email_text = UX::grabPage('text_snippets/email_feedback', $e, true);

	$mail = Mailer::send(array('email' => 'summerprogram@cis.edu.hk', 'name' => 'CIS Summer Program'), '[Feedback Submitted]', $email_text);

} else {
	$b['blurb'] = '<p>We welcome all comments, suggestions, and complaints. If you have a pressing issue that requires immeidate attention, please call or email our office instead: details are on the <a href="/">home page</a>. Thank you for helping us improve.</p>';
}

// Include header section
echo UX::makeHead($h, $n);

// The page
echo UX::grabPage('dev/feedback', $b, true);

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

echo UX::grabPage('common/masthead', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>