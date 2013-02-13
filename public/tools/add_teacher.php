<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

header('Content-Type: text/plain');

require_once('../../private/php/class.data.php');
require_once('../../private/php/class.security.php');
require_once('../../private/php/class.acl.php');

echo ACL::addTeacherSafe('yectep@gmail.com', 'Chester Li');

?>