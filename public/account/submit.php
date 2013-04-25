<?php

/**
 * Check/submit schedule
 */


define('PTP',   '../../private/');
define('PHX_SCRIPT_TYPE',   'HTML');
define('PHX_UX',        true);
define('PHX_STUDENT',   true);
define('PHX_COURSES',   true);
define('PHX_MAILER',    true);


// Include common ignition class
require_once(PTP . 'php/ignition.php');

// We require a staff login for this page
if (!ACL::checkLogin('public')) {
    header('Location: ./login.php?msg=error_nologin');
    exit();
} else {
    $_fam = new FamStu('family', $_SESSION['SSOID']);
    $_fam->getChildren();
    if (sizeof($_fam->children) == 0) {
        header('Location: /account/add_child.php?msg=new_acct');
        exit();
    }

    if (!array_key_exists('STUID', $_SESSION)) {
        header('Location: /account/dashboard.php?msg=no_active');
        exit();
    }

    $_stu = new FamStu('student', $_SESSION['STUID']);
    if ($_stu->data['FamilyID'] !== $_fam->fid) {
        header('Location: /account/dashboard.php?msg=child_exception');
        exit();
    } else {
        $p['sid'] = $_stu->sid;
    }
}

if ($_stu->data['StudentSubmitted'] == '0') {
    if ($_GET['key'] == sha1($_stu->data['StudentPrivateKey'])) {
        $stmt = Data::prepare('UPDATE `students` SET `StudentSubmitted` = 1, `StudentSubmitTS` = NOW() WHERE `StudentID` = :sid LIMIT 1');
        $stmt->bindParam('sid', $_stu->sid, PDO::PARAM_INT);
        $stmt->execute();
        Mailer::send(array('email' => $_fam->getFamEmail(), 'name' => $_fam->data['FamilyName']), '[CIS Summer] Schedule Submitted', UX::grabPage('text_snippets/email_submitted', array('name' => $_stu->data['StudentNameGiven'].' '.$_stu->data['StudentNameLast']), false));
        $api = new MCAPI(MAILCHIMP_KEY);
        $merge_vars = array('FNAME'=>$_fam->data['FamilyName']);
        $retval = $api->listSubscribe('0816291f28', $_fam->getFamEmail(), $merge_vars, 'html', false, true, true, false );
        Common::logAction('http.submit.registration', 'success', 'STUID='.$_stu->sid, 'Submitted');

        if ($api->errorCode){
            Common::logAction('http.submit.mailinglist', 'failure', 'STUID='.$_stu->sid, 'Err Msg: '.$api->errorMessage);
        }
        
        $inc = 'account/submit_success';
    } else {
        // Confirm!
        $p['key'] = sha1($_stu->data['StudentPrivateKey']);
        $inc = 'account/submit_confirm';
    }
} else {
    $p['submit_ts'] = date(DATETIME_FULL, strtotime($_stu->data['StudentSubmitTS']));
    $inc = 'account/submit_review';
}

// Page variables (common)
$p['student_name'] = $_stu->data['StudentNamePreferred'].' '.$_stu->data['StudentNameLast'];

$h['title'] = 'Submit Registration | '.$_stu->data['StudentNamePreferred'];

// Include header section
echo UX::makeHead($h, $n, 'common/header_public', 'common/nav_account');

// Page info
echo UX::makeBreadcrumb(array(  'My Family' => "/account/dashboard.php", $_stu->data['StudentNamePreferred'].'\'s Profile &amp; Schedule' => "/account/view_student.php?sid=".$_GET['sid'], 'Submit Registration' => "/account/submit.php"));
echo UX::grabPage($inc, $p, true);

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

echo UX::grabPage('common/masthead', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>

