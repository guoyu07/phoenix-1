<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

header('Content-Type: text/plain');

echo "HASHDUMP\n";

require_once('../../private/php/class.security.php');
require_once('../../private/php/class.acl.php');

$pass = ACL::getHash($_GET['password']);

print_r($pass);

echo "\n\nReverse password check:\n";

if (ACL::checkHash($_GET['password'], $pass)) echo 'Hash is okay';
else echo 'Has is not okay';
echo "\n";

die();

?>