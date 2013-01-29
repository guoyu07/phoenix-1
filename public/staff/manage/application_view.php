<?php

/**
 * Course applications - view a single application
 * based on MySQL JSON data.
 *
 * @author  Yectep Studios <info@yectep.hk>
 * @version 30104
 * @package Plume
 * @subpackage Staff
 */


define('PTP',   '../../../private/');
define('PHX_SCRIPT_TYPE',   'HTML');
define('PHX_UX',        true);
define('PHX_LAOSHI',    true);
define('PHX_COURSES',   true);


// Include common ignition class
require_once(PTP . 'php/ignition.php');

// We require a staff login for this page
if (!ACL::checkLogin('staff')) {
    header('Location: /staff/index.php?msg=error_nologin');
    exit();
} else {
    $_laoshi = new Laoshi($_SESSION['SSOID']);
    $_laoshi->perms(6, 7, 8);
}

// Get application id
$app_data = Courses::getAppDataById($_REQUEST['id']);

if (!$app_data) {
    header('Location: ./applications.php');
    exit();
} else {
	echo "<!--";
    var_dump($app_data);
    echo "-->";
    // exit();
}

// Triage and get default staff page
$h['title'] = $app_data['course_info']['course_name'];
$n['management'] = 'active';
$n['my_name'] = $_laoshi->staff['StaffName'];

// Include header section
echo UX::makeHead($h, $n, 'common/header_staff', $_laoshi->fetchNavPage());

// Specified page items
$p = $app_data['course_info'];
$p['teacher_suppl'] = (($p['teacher_suppl'] == "") ? '<em class="muted">None specified</em>' : $p['teacher_suppl']);
$p['subject_code'] = (($p['program'] == 'AP') ? $p['ap_subject'] : $p['sp_subject']);
$p['subject_name'] = Courses::getSubjectName($p['subject_code']);
$p['class_json'] = json_encode($app_data['class_info']);
$p['course_json'] = json_encode($app_data);

// Get roomlist
$rooms = Courses::getRoomList();
$p['rooms'] = '';
foreach($rooms as $roomid => $rm) {
    $p['rooms'] .= '<option value="'.$roomid.'">'.$rm['name'].'</option>'."\n";
}


// Page info
echo UX::makeBreadcrumb(array(  'Staff Portal'      => '/staff/dashboard.php', 'Course Applications' => "/staff/manage/applications.php", $app_data['course_info']['course_name'] => '/staff/manage/view_application.php?id='.$_REQUEST['id']));
echo UX::grabPage('staff/manage/application_view', $p, true);

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

echo UX::grabPage('common/masthead_staff', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>

