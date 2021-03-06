<?php

/**
 * CSS style definition guide as POC
 *
 * @author	Yectep Studios <info@yectep.hk>
 * @version	20707
 * @package Plume
 */


define('PTP',   '../../private/');
define('PHX_SCRIPT_TYPE',   'HTML');
define('PHX_NEWS',      true);
define('PHX_UX',        true);
define('PHX_MAILER',    true);


// Include common ignition class
require_once(PTP . 'php/ignition.php');

// Set page switch variables
$h['title'] = 'Create an Account';
$n['register'] = 'active';

// Is reg closed?
if (!REGISTRATION_OPEN && !array_key_exists('override', $_GET)) {
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
$error = '<div class="alert alert-blue">We are nearing the end of the program. We suggest that you visit our office to sign up on the spot as spaces are now extremely limited.</div>';

// Check for POST data
if ($_POST) {
    // Form pre-filled elsewhere?
    if (array_key_exists('o', $_GET)) {
        $error = '<div class="alert alert-green"><img src="/assets/icons/tick.png" title="[OK]" /> Great work on starting your family account! Please fill in the rest of the form and submit it. Thank you.</div>';
    } else {
        // Validate form
        if (!isset($_POST['name']) || !isset($_POST['pass']) || !isset($_POST['email']) || !isset($_POST['confirm']) || !isset($_POST['locale']) || !isset($_POST['hphone']) || !isset($_POST['address']) || !isset($_POST['lang']) || !isset($_POST['transport'])) {
            // Items missing
            $error = '<div class="alert alert-red">Whoops, it looks like you\'re missing a few items. Please fill in the rest of the form. Thanks!</div>';
        } else {
        
            // Check others
            
            if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                // Invalid email
                $error = '<div class="alert alert-red">The email address you used is invalid. Please try again.</div>';
                $_POST['email'] = '';
                
                
            } else if (ACL::checkSsoEmail($_POST['email'], 'public')) {
                // Email exists
                $error = '<div class="alert alert-red">The email address you used (<strong>'.$_POST['email'].'</strong>) is already registered. If you own this account, please login to add a child to your account.</div>';
                $_POST['email'] = '';
                
                
            } else if ($_POST['pass'] !== $_POST['confirm']) {
                // Passwords don't match
                $error = '<div class="alert alert-red">The two passwords do not match. Please try again.</div>';
                
                
            } else if (isset($_POST['mphone']) && (filter_var($_POST['mphone'], FILTER_VALIDATE_INT, array('options' => array('min_range' => 50000000, 'max_range' => 99999999))) === false)) {
                // Mobile phone provided but not HK number
                $error = '<div class="alert alert-red">You did not input a <a href="javascript:;" onclick="$.scrollTo($(\'input[name=mphone]\'), 300, { offset: {top: -90}, onAfter: function() { $(\'input[name=mphone]\').focus(); } });"><strong>Hong Kong mobile phone number</strong></a>. As a reminder, you do not need the +852 calling code. If you do not have a local mobile phone yet, you may add this later in your account settings</div>';
                $_POST['mphone'] = '';
                
                
            } else if (($_POST['notify_sms'] == 1) && (!isset($_POST['mphone']))) {
                // Wanted SMS notifications but no phone number
                $error = '<div class="alert alert-red">You did not input a <a href="javascript:;" onclick="$.scrollTo($(\'input[name=mphone]\'), 300, { offset: {top: -90}, onAfter: function() { $(\'input[name=mphone]\').focus(); } });"><strong>Hong Kong mobile phone number</strong></a> to enable SMS notifications. Either enter a mobile number, or disable SMS notifications. You can change this preference later.</div>';
                $_POST['mphone'] = '';
                
            } else {
            
                // Add SSO object first!
                if (ACL::makeSsoObject($_POST['email'], $_POST['pass'], 'family', 'public')) {
                    $obj = ACL::checkSsoEmail($_POST['email'], 'public');

                    $country = (($_POST['locale'] == "HK") ? "HK" : "NO");
                    $sms = (($_POST['notify_sms'] == 1) ? 1 : 0);
                    $mphone = ((isset($_POST['mphone']) || (strlen($_POST['mphone']) !== 8)) ? $_POST['mphone'] : '00000000');
                    
                    try {
                        $stmt = Data::prepare("INSERT INTO `families` (`ObjID`, `FamilyAccountStatus`, `FamilyCTS`, `FamilyLATS`, `FamilyLLTS`, `FamilyName`, `FamilyAddress`, `FamilyCountry`, `FamilyPhoneHome`, `FamilyPhoneMobile`, `FamilyLanguage`, `FamilyTransport`) VALUES (:objid, 0, NOW(), NOW(), NOW(), :name, :addr, :cty, :hphone, :mphone, :lang, :trans)");
                        $stmt->bindParam('objid', $obj, PDO::PARAM_INT);
                        $stmt->bindParam('name', $_POST['name'], PDO::PARAM_STR);
                        $stmt->bindParam('addr', $_POST['address'], PDO::PARAM_STR);
                        $stmt->bindParam('cty', $country);
                        $stmt->bindParam('hphone', $_POST['hphone'], PDO::PARAM_STR);
                        $stmt->bindParam('mphone', $mphone);
                        $stmt->bindParam('lang', $_POST['lang']);
                        $stmt->bindParam('trans', $_POST['transport']);
                        $stmt->execute();

                        // Log it!
                        Common::logAction('http.post.register', 'success', 'EMAIL='.$_POST['email'].';SSOID='.$obj, '');

                        $ssoobj = ACL::getSsoObject($obj);
                        $pass = sha1($ssoobj['ObjHash']);
                        
                        // Great, now send the email
                        $body = UX::grabPage('text_snippets/email_register', array('name' => $_POST['name'], 'email' => $_POST['email'], 'shasalt' => $pass), false);
                        $mail = Mailer::send(array('email' => $_POST['email'], 'name' => $_POST['name']), '[Action Required] Verify Your Account', $body);
                        header('Location: ./login.php?msg=registered');
                        exit();
                    } catch (PDOException $e) {
                        Common::throwNiceDataException($e);
                    }
                } else {
                    Common::niceException('Error in creating a login object for your account. Please try again or notify staff of this error');
                }
            }
        }
    }
}


// Include header section
echo UX::makeHead($h, $n);

// Page info
echo UX::makeBreadcrumb(array(	'Create Family Account'		=> '/account/register.php'));
echo UX::grabPage('account/register', array('error' => $error, 'form_json' => json_encode($_POST)), true);

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

echo UX::grabPage('common/masthead', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>