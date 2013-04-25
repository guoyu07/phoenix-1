<?php

/**
 * Courses - with or without a selected child...
 *
 * @author  Yectep Studios <info@yectep.hk>
 * @version 20825
 * @package Plume
 */


define('PTP',   '../../private/');
define('PHX_SCRIPT_TYPE',   'HTML');
define('PHX_UX',        true);
define('PHX_STUDENT',   true);
define('PHX_COURSES',   true);

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
        header('Location: /account/dashboard.php');
        exit();
    }

    $_stu = new FamStu('student', $_SESSION['STUID']);
    if ($_stu->data['FamilyID'] !== $_fam->fid) {
        header('Location: /account/dashboard.php');
        exit();
    }
}


// Triage and get default staff page
$h['title'] = 'Course Selection | '.$_stu->data['StudentNamePreferred'];
$n['courses'] = 'active';
$n['my_name'] = $_fam->data['FamilyName'];

// Page replacements
$p['student_name'] = $_stu->data['StudentNamePreferred'].' '.$_stu->data['StudentNameLast'];
$p['age'] = $_stu->data['StudentAge'];

// Include header section
echo UX::makeHead($h, $n, 'common/header_public', 'common/nav_account');

// Page info
echo UX::makeBreadcrumb(array(  'My Family' => "/account/dashboard.php", 'Active Student: '.$_stu->data['StudentNamePreferred'].' '.$_stu->data['StudentNameLast'] => "/account/view_student.php?sid=".$_stu->sid, 'Course Selection' => "/account/courses.php/#!/show:SP"));

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

// Page output
echo UX::grabPage('account/courses', $p, true);

echo UX::grabPage('common/masthead', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>