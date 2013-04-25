<?php

/**
 * View student data and schedule
 *
 * @author  Yectep Studios <info@yectep.hk>
 * @version 30104
 * @package Plume
 */


define('PTP',   '../../private/');
define('PHX_SCRIPT_TYPE',   'HTML');
define('PHX_UX',        true);
define('PHX_STUDENT',   true);
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
    }
}


$p['student_name'] = $_stu->data['StudentNamePreferred'].' '.$_stu->data['StudentNameLast'];
$p['sid'] = $_stu->sid;

// Update!
try {
    $stmt = Data::prepare('UPDATE `students` SET `StudentNameLast` = :lname, `StudentNamePreferred` = :pname, `StudentNameGiven` = :fname, `StudentMedCondition` = :cond, `StudentMedMedications` = :meds WHERE `StudentID` = :sid LIMIT 1');
    $stmt->bindParam('sid', $_stu->sid, PDO::PARAM_INT);
    $stmt->bindParam('fname', substr($_POST['fname'], 0, 255), PDO::PARAM_STR);
    $stmt->bindParam('lname', substr($_POST['lname'], 0, 255), PDO::PARAM_STR);
    $stmt->bindParam('pname', substr($_POST['pname'], 0, 255), PDO::PARAM_STR);
    $stmt->bindParam('meds', $_POST['med_mds'], PDO::PARAM_STR);
    $stmt->bindParam('cond', $_POST['med_cond'], PDO::PARAM_STR);
    $stmt->execute();

    Mailer::send(array('email' => $_fam->getFamEmail(), 'name' => $_fam->data['FamilyName']), '[CIS Summer] Student Details Updated', UX::grabPage('text_snippets/email_update_student', array('name' => $_stu->data['StudentNameGiven'].' '.$_stu->data['StudentNameLast']), false));
} catch (PDOException $e) {
    echo UX::grabPage('dev/error', array('pretext' => $e->getMessage()));
    exit();
}

// Include header section
echo UX::makeHead($h, $n, 'common/header_public', 'common/nav_account');

// Page info
echo UX::makeBreadcrumb(array(  'My Family' => "/account/dashboard.php", $_stu->data['StudentNamePreferred'].'\'s Profile &amp; Schedule' => "/account/view_student.php?sid=".$_GET['sid']));
echo UX::grabPage('account/edit_student', $p, true);

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

echo UX::grabPage('common/masthead', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>

