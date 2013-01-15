<?php

/**
 * Course applications
 *
 * @author  Yectep Studios <info@yectep.hk>
 * @version 30104
 * @package Plume
 * @subpackage Staff
 */


define('PTP',   '../../private/');
define('PHX_SCRIPT_TYPE',   'HTML');
define('PHX_UX',        true);
define('PHX_LAOSHI',    true);


// Include common ignition class
require_once(PTP . 'php/ignition.php');

// We require a staff login for this page
if (!ACL::checkLogin('staff')) {
    header('Location: ./index.php?msg=error_nologin');
    exit();
} else {
    $_laoshi = new Laoshi($_SESSION['SSOID']);
    $_laoshi->perms(16);
}

// Triage and get default staff page
$h['title'] = 'Event Log';
$n['my_name'] = $_laoshi->staff['StaffName'];

// Prepare WHERE statement
$where_array = array();

if (isset($_GET['perp'])) {
    $where_array[] = '`LogIdent` = "'.base64_decode($_GET['perp']).'"';
}

if (isset($_GET['addr'])) {
    $where_array[] = '`LogIP` = "'.$_GET['addr'].'"';
}

$where = ((sizeof($where_array) > 0) ? ' WHERE '.implode(' AND ', $where_array) : '');

// Get log data
$stmt = Data::prepare('SELECT *, DATE_FORMAT(CONVERT_TZ(`LogTS`, "+00:00", "+08:00"), "%d%b%y %H:%i:%s") as `LogTS` FROM `log`'.$where.' ORDER BY LogTS DESC LIMIT 0,50');
$stmt->execute();
$logdata = $stmt->fetchAll(PDO::FETCH_ASSOC);

$logtable = '';

foreach($logdata as $event) {
    $logtable .= "\t\t<tr>
            <td>".strtoupper($event['LogTS'])."</td>
            <td><a href=\"./view_log.php?addr=".$event['LogIP']."\">".$event['LogIP']."</a><br /><span class=\"muted small\">".$event['LogGeoResult']."</span></td>
            <td>".(($event['LogIdent'] == 'UNK=unknown') ? '<span class="muted"><em>Not recorded</em></span>' : '<a href="./view_log.php?perp='.base64_encode($event['LogIdent']).'">'.$event['LogIdent'].'</a>')."</td>
            <td>[".$event['LogAction'].": ".$event['LogResult']."] ".$event['LogRemarks']."</td>
        </tr>\n";
}


// Include header section
echo UX::makeHead($h, $n, 'common/header_staff', $_laoshi->fetchNavPage());

// Page info
echo UX::makeBreadcrumb(array(	'Staff Portal'		=> '/staff/',
                                'Event Viewer'  => '/staff/view_log.php'));
echo UX::grabPage('staff/view_log', array('logs' => $logtable), true);

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

echo UX::grabPage('common/masthead', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>