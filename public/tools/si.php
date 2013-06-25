<?php


define('PTP',   '../../private/');
define('PHX_SCRIPT_TYPE',   'HTML');
define('PHX_UX',        true);
define('PHX_MAILER',    true);


// Include common ignition class
require_once(PTP . 'php/ignition.php');

// Check that current person exists
if (!isset($_GET) || !array_key_exists('hid', $_GET)) {
    echo UX::grabPage('qrscan/invalid', null, true);
    exit();
}

$stmt = Data::prepare('SELECT * FROM `helpers_accounts` WHERE `HelperID` = :hid LIMIT 1');
$stmt->bindParam('hid', $_GET['hid']);
$stmt->execute();
$person = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (sizeof($person) !== 1) {
    // No such person
    echo UX::grabPage('qrscan/no_such_person', null, true);
    exit();
} else {
    var_dump($person);
}

$stmt = Data::prepare('SELECT * FROM `helpers_checkins` WHERE `HelperID` = :hid AND `CheckinDay` = NOW() LIMIT 1');
$stmt->bindParam('hid', $_GET['hid']);
$stmt->execute();
$signout = $stmt->fetch(PDO::FETCH_ASSOC);

if (sizeof($signout) == 0) {
    // Sign in for today
    if ($_COOKIE['SignIn']) {
        // Already checked for another person....
        $body = UX::grabPage('text_snippet/email_multi_signin', $p, true);
        Mailer::send(array('name' => 'Summer Program', 'email'=>'summerprogram@cis.edu.hk'), '[QR Signin] Multiple Signin Detected', $body, $fromText = "CIS Summer Program", $fromAddr = "noreply@summer.cis.edu.hk");
    } else {
        // Clear - signin
        $stmt = Data::prepare('INSERT INTO `helpers_checkins` (`HelperID`, `CheckinDay`, `CheckinTimeIn`) VALUES (:hid, NOW(), NOW())');
        $stmt->bindParam('hid', $_GET['hid']);
        $stmt->execute();
        // Sign in recorded, display information
        $p['timestamp'] = date(DATETIME_FULL);
    }
}

?>

