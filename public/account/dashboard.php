<?php

/**
 * Family dashboard
 *
 * @author  Yectep Studios <info@yectep.hk>
 * @version 30104
 * @package Plume
 */


define('PTP',   '../../private/');
define('PHX_SCRIPT_TYPE',   'HTML');
define('PHX_UX',        true);
define('PHX_STUDENT',    true);


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

}

// Triage and get default staff page
$h['title'] = 'My Dashboard';
$n['dashboard'] = 'active';
$n['my_name'] = $_fam->data['FamilyName'];

// Page variables
$p['child_count'] = sizeof($_fam->children);
$p['children'] = '';

// Child additional
foreach($_fam->children as $child) {
    $dobDo = new DateTime($child['StudentDOB']);
    $todayDo = new DateTime('00:00:00');

    $i['name'] = $child['StudentNamePreferred'].' '.$child['StudentNameLast'];
    $i['dob'] = $child['StudentDOB'];
    $i['age'] = $todayDo->diff($dobDo)->y;
    $i['sid'] = $child['StudentID'];

    $p['children'] .= (($child['StudentSubmitted'] == 1) ? UX::grabPage('account/child_snippet_submitted', $i, false) : UX::grabPage('account/child_snippet', $i, false));
}


// Include header section
echo UX::makeHead($h, $n, 'common/header_public', 'common/nav_account');

// Page info
echo UX::makeBreadcrumb(array(  'My Family Dashboard' => "/account/dashboard.php"));
echo UX::grabPage('account/dashboard', $p, true);

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

echo UX::grabPage('common/masthead', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>

