<?php


define('PTP',   '../../private/');
define('PHX_SCRIPT_TYPE',   'HTML');
define('PHX_UX',        true);
define('PHX_MAILER',    true);


// Include common ignition class
require_once(PTP . 'php/ignition.php');

// Check that current person exists
if ($_SERVER['QUERY_STRING'] == 'magic') {
    setcookie('SignIn', 0, time()-3600, '/');
    echo UX::grabPage('text_snippets/qr', array('msg' => 'Magic eraser success!'), true);
    exit();
}
if (!isset($_GET) || !array_key_exists('hid', $_GET)) {
    echo UX::grabPage('qrscan/invalid', null, true);
    exit();
}

$stmt = Data::prepare('SELECT * FROM `helpers_accounts` WHERE `HelperID` = :hid LIMIT 1');
$stmt->bindParam('hid', $_GET['hid']);
$stmt->execute();
$person = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$person) {
    // No such person
    echo UX::grabPage('text_snippets/qr_error', array('msg' => 'Error: <strong>No such helper found</strong>!<br />Go see one of the staff please!'), true);
    exit();
}


try {
    $stmt = Data::prepare('SELECT * FROM `helpers_checkins` WHERE `HelperID` = :hid AND `CheckinDay` = DATE(NOW()) ORDER BY `CheckinTimeOut` DESC LIMIT 1');
    $stmt->bindParam('hid', $_GET['hid']);
    $stmt->execute();
    $signout = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo 'Error: '.$e->getMessage();
    exit();
}

if (!$signout) {
    
    // Sign in for today
    if (($_COOKIE['SignIn'] > 0) && ($_COOKIE['SignIn'] !== $_GET['hid'])) {
        // Already checked for another person....
        $stmt = Data::prepare('SELECT * FROM `helpers_accounts` WHERE `HelperID` = :hid LIMIT 1');
        $stmt->bindParam('hid', $_COOKIE['SignIn']);
        $stmt->execute();
        $person2 = $stmt->fetch(PDO::FETCH_ASSOC);

        $body = "QR scanning mechanism detected a multiple signin.

Signed in as: ".$person2['HelperName']."
Tried to sign in as: ".$person['HelperName']."

Might wanna get that checked out ;)";

        Mailer::send(array('name' => 'Summer Program', 'email'=>'summerprogram@cis.edu.hk'), '[QR Signin] Multiple Signin Detected', $body);
        echo UX::grabPage('text_snippets/qr_error', array('msg' => 'Error: <strong>Multiple sign-in detected</strong>!<br />Go see one of the staff please!'), true);
        exit();
    } else {
        // Clear - signin
        $stmt = Data::prepare('INSERT INTO `helpers_checkins` (`HelperID`, `CheckinDay`, `CheckinTimeIn`) VALUES (:hid, DATE(NOW()), NOW())');
        $stmt->bindParam('hid', $_GET['hid']);
        $stmt->execute();
        // Sign in recorded, display information
        $p['timestamp'] = date(DATETIME_FULL);
        $p['name'] = $person['HelperName'];
        setcookie('SignIn', $person['HelperID'], time()+3600*12, '/');
        echo UX::grabPage('text_snippets/qr', array('msg' => 'Signed <strong>'.$p['name'].'</strong> in at<br />'.$p['timestamp']), true);
        exit();
    }

} elseif ((time()-strtotime($signout['CheckinTimeIn'])-3600*12) < 600) {

    // Sign in for today
    if (($_COOKIE['SignIn'] > 0) && ($_COOKIE['SignIn'] !== $_GET['hid'])) {
        // Already checked for another person....
        $stmt = Data::prepare('SELECT * FROM `helpers_accounts` WHERE `HelperID` = :hid LIMIT 1');
        $stmt->bindParam('hid', $_COOKIE['SignIn']);
        $stmt->execute();
        $person2 = $stmt->fetch(PDO::FETCH_ASSOC);

        $body = "QR scanning mechanism detected a multiple signin.

Signed in as: ".$person2['HelperName']."
Tried to sign in as: ".$person['HelperName']."

Might wanna get that checked out ;)";

        Mailer::send(array('name' => 'Summer Program', 'email'=>'summerprogram@cis.edu.hk'), '[QR Signin] Multiple Signin Detected', $body);
        echo UX::grabPage('text_snippets/qr_error', array('msg' => 'Error: <strong>Multiple sign-in detected</strong>!<br />Go see one of the staff please!'), true);
        exit();

    } else {
        // Sign in recorded, display information
        $p['timestamp'] = date(DATETIME_FULL);
        $p['name'] = $person['HelperName'];
        setcookie('SignIn', $person['HelperID'], time()+3600*12, '/');
        echo UX::grabPage('text_snippets/qr', array('msg' => 'Signed <strong>'.$p['name'].'</strong> in at<br />'.$p['timestamp']), true);
        exit();
    }

} else {
    // Signout
    $stmt = Data::prepare('UPDATE `helpers_checkins` SET `CheckinTimeOut` = NOW() WHERE `HelperID` = :hid AND `CheckinDay` = DATE(NOW())');
    $stmt->bindParam('hid', $_GET['hid']);
    $stmt->execute();
    $p['timestamp'] = date(DATETIME_FULL);
    $p['name'] = $person['HelperName'];
    setcookie('SignIn', 0, time()-3600, '/');
    echo UX::grabPage('text_snippets/qr', array('msg' => 'Signed <strong>'.$p['name'].'</strong> out at<br />'.$p['timestamp']), true);
    exit();
}

?>

