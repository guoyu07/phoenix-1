<?php

/**
 * SSO reset page - for both staff and parent accounts
 *
 * @author  Yectep Studios <info@yectep.hk>
 * @version 30405
 * @package Plume
 */

define('PTP',   '../private/');
define('PHX_SCRIPT_TYPE',   'HTML');
define('PHX_MAILER',    true);
define('PHX_UX',        true);


// Include common ignition class
require_once(PTP . 'php/ignition.php');

// Set page switch variables
$h['title'] = 'Password Recovery';

if (isset($_POST['email']) && isset($_POST['type'])) {
    // Initialize reset action
    // First search for SSO
    $objid = ACL::checkSsoEmail($_POST['email'], $_POST['type']);
    $obj = ACL::getSsoObject($objid);

    if (!$obj) {
        // SSO object doesn't exist
        $return = UX::grabPage('account/sso_reset_noemail', array('email' => $_POST['email'], 'type' => $_POST['type']), true);
    } else {
        //var_dump($obj);

        // Account exists
        // Generate code
        $e['vericode'] = strtoupper(md5($obj['ObjHash']));
        $e['id'] = $obj['ObjID'];

        $email_text = UX::grabPage('text_snippets/email_sso_reset', $e, true);

        $mail = Mailer::send(array('email' => $obj['ObjEmail'], 'name' => 'Account Holder'), '[CIS Summer] Password Recovery Details', $email_text);
        $msg = '<div class="alert alert-green"><img src="/assets/icons/tick.png" /> We\'ve sent a message to <strong>'.$obj['ObjEmail'].'</strong> with your account verification code. It should arrive shortly.</div>';
        $return = UX::grabPage('account/sso_reset_found', array('msg' => $msg, 'id' => $obj['ObjID']), true);
    }

} elseif (isset($_POST['ssoid']) && isset($_POST['vericode'])) {
    // Find SSO...
    $obj = ACL::getSsoObject($_POST['ssoid']);
    if (!obj) {
        $return = UX::grabPage('dev/permissions', null, true);
    } elseif (strtoupper(md5($obj['ObjHash'])) !== strtoupper(trim($_POST['vericode']))) {
        // Wrong vericode
        $msg = '<div class="alert alert-red"><img src="/assets/icons/cross.png" /> Sorry, the Verification Code you typed was incorrect. Please make sure you copy and paste the code as given in the email directly, or use the link instead.</div>';
        $return = UX::grabPage('account/sso_reset_found', array('msg' => $msg, 'id' => $obj['ObjID']), true);
    } else {
        $return = UX::grabPage('account/sso_reset_newpass', array('email' => $obj['ObjEmail'], 'rec_key' => sha1($obj['ObjHash']), 'id' => $obj['ObjID']), true);
    }
} elseif (isset($_GET['ssoid']) && isset($_GET['c'])) {
    // Find SSO...
    $obj = ACL::getSsoObject($_GET['ssoid']);
    if (!obj) {
        $return = UX::grabPage('dev/permissions', null, true);
    } elseif (strtoupper(md5($obj['ObjHash'])) !== strtoupper(trim($_GET['c']))) {
        // Wrong vericode
        $msg = '<div class="alert alert-red"><img src="/assets/icons/cross.png" /> Sorry, the Verification Code you typed was incorrect. Please make sure you copy and paste the code as given in the email directly, or use the link instead.</div>';
        $return = UX::grabPage('account/sso_reset_found', array('msg' => $msg, 'id' => $obj['ObjID']), true);
    } else {
        $return = UX::grabPage('account/sso_reset_newpass', array('email' => $obj['ObjEmail'], 'rec_key' => sha1($obj['ObjHash']), 'id' => $obj['ObjID']), true);
    }
} elseif (isset($_POST['ssoid']) && isset($_POST['rec_key'])) {
    // Find SSO...
    $obj = ACL::getSsoObject($_POST['ssoid']);
    if (!obj) {
        $return = UX::grabPage('dev/permissions', null, true);
    } elseif (sha1($obj['ObjHash']) !== $_POST['rec_key']) {
        // Wrong vericode
        $return = UX::grabPage('dev/permissions', null, true);
    } else {
        // Reset password
        ACL::updatePassword($_POST['ssoid'], $_POST['pass']);

        // Send to the correct destination
        if ($obj['ObjPortal'] == 'staff') {
            header("Location: /staff/?msg=pass_change");
            exit();
        } else {
            header("Location: /account/login.php?msg=pass_change");
            exit();
        }
    }
} else {
    $return = UX::grabPage('dev/permissions', null, true);
}

// Include header section
echo UX::makeHead($h, $n);

// The page
echo $return;

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

echo UX::grabPage('common/masthead', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>