<?php

/**
 * CSS style definition guide as POC
 *
 * @author	Yectep Studios <info@yectep.hk>
 * @version	20707
 * @package Plume
 */


define('PTP',   '../../private/');
define('BETA',  true);
define('PHX_SCRIPT_TYPE',   'HTML');
define('PHX_NEWS',      true);
define('PHX_UX',        true);


// Include common ignition class
require_once(PTP . 'php/ignition.php');

// Set page switch variables
$h['title'] = 'Create an Account';
$n['register'] = 'active';

// Is reg closed?
if (!REGISTRATION_OPEN) {
    // Include header section
    echo UX::makeHead($h, $n);
    
    // Page info
    echo UX::makeBreadcrumb(array(	'Create Family Account'		=> '/account/register.php'));
    echo UX::grabPage('account/regclosed', null, true);
    
    // Before footer grab time spent
    $t['end'] = microtime(true);
    $time = round(($t['end'] - $t['start']), 3);
    
    echo UX::grabPage('common/masthead', array('time' => $time), true);
    echo UX::grabPage('common/footer', null, true);
    
    exit();
}

// Default message
$error = '<div class="alert"><strong>Hey kids!</strong> If you don\'t know how to fill out this form, let your parents help! They\'ll make sure you fill in everything correctly. Thanks!</div>';

// Check for POST data
if ($_POST) {
    // Form pre-filled elsewhere?
    if (array_key_exists('o', $_GET)) {
        $error = '<div class="alert alert-green"><img src="/assets/icons/tick.png" title="[OK]" /> Great work on starting your family account! Please fill in the rest of the form and submit it. Thank you.</div>';
    } else {
        // Validate form
        if (!isset($_POST['name']) || !isset($_POST['pass']) || !isset($_POST['email']) || !isset($_POST['confirm']) || !isset($_POST['locale']) || !isset($_POST['hphone']) || !isset($_POST['address'])) {
            // Items missing
            $error = '<div class="alert alert-red">Whoops, it looks like you\'re missing a few items. Please fill in the rest of the form. Thanks!</div>';
        } else {
            // Check others
            if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                // Invalid email
                $error = '<div class="alert alert-red">The email address you used is invalid. Please try again.</div>';
                $_POST['email'] = '';
            } else if (Security::checkEmail($_POST['email'])) {
                // Email exists
                $error = '<div class="alert alert-red">The email address you used (<strong>'.$_POST['email'].'</strong>) is already registered. If you own this account, please login to add a child to your account.</div>';
                $_POST['email'] = '';
            } else if (isset($_POST['mphone']) && (filter_var($_POST['mphone'], FILTER_VALIDATE_INT, array('options' => array('min_range' => 50000000, 'max_range' => 99999999))) === false)) {
                $error = '<div class="alert alert-red">You did not input a <a href="javascript:;" onclick="$.scrollTo($(\'input[name=mphone]\'), 300, { offset: {top: -90}, onAfter: function() { $(\'input[name=mphone]\').focus(); } });"><strong>Hong Kong mobile phone number</strong></a>. As a reminder, you do not need the +852 calling code. If you do not have a local mobile phone yet, you may add this later in your account settings</div>';
                $_POST['mphone'] = '';
            } else {
                die('Great');
                $stmt = Data::prepare('');
            }
        }
    }
}

// Get countries
$stmt = Data::query('SELECT * FROM `countries` ORDER BY CountryName ASC');
$stmt->execute();
$countries = $stmt->fetchAll(PDO::FETCH_ASSOC);
$coptions = '';

foreach($countries as $country) {
    $coptions .= "\t\t\t\t<option value=\"".$country['CountryISO']."\">".$country['CountryName']."</option>\n";
}


// Include header section
echo UX::makeHead($h, $n);

// Page info
echo UX::makeBreadcrumb(array(	'Create Family Account'		=> '/account/register.php'));
echo UX::grabPage('account/register', array('error' => $error, 'countries' => $coptions, 'form_json' => json_encode($_POST)), true);

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

echo UX::grabPage('common/masthead', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>