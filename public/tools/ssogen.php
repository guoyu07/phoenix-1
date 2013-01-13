<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

header('Content-Type: text/plain');

require_once('../../private/php/class.data.php');
require_once('../../private/php/class.security.php');
require_once('../../private/php/class.acl.php');

if ($_GET['method'] == 'make') {
    echo ACL::makeSsoObject($_GET['email'], $_GET['pass'], 'administrator', $_GET['portal']);
} elseif ($_GET['method'] == 'check') {
    $login = ACL::login($_GET['email'], $_GET['pass'], $_GET['portal']);
    if ($login) {
        echo 'Login was successful';
    } elseif ($login === null) {
        echo 'Account does not exist in portal: '.$_GET['portal'];
    } elseif ($login === false) {
        echo 'Password is wrong';
    } else {
        echo 'Strange error occured';
    }
}

?>