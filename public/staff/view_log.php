<?php

/**
 * Staff signin page
 *
 * @author	Yectep Studios <info@yectep.hk>
 * @version	20707
 * @package Plume
 * @subpackage Staff
 */


define('PTP',   '../../private/');
define('PHX_SCRIPT_TYPE',   'HTML');
define('PHX_UX',        true);


// Include common ignition class
require_once(PTP . 'php/ignition.php');

// Set page switch variables
$h['title'] = 'Sign In';
$n['staffsignin'] = 'active';

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
$stmt = Data::prepare('SELECT *, DATE_FORMAT(CONVERT_TZ(`LogTS`, "+00:00", "+08:00"), "%d%b%y %H:%i:%s") as `LogTS` FROM `log`'.$where);
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
echo UX::makeHead($h, $n);

// Page info
echo UX::makeBreadcrumb(array(	'Staff Center'		=> '/staff/',
                                'Event Viewer'  => '/staff/view_log.php'));
echo UX::grabPage('staff/view_log', array('logs' => $logtable), true);

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

echo UX::grabPage('common/masthead', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>