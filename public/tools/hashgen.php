<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once('../../private/php/class.security.php');
require_once('../../private/php/class.acl.php');

if (isset($_POST['password'])) {
    header('Content-Type: text/plain');

    echo "HASHDUMP\n";

    $pass = ACL::getHash($_POST['password']);

    print_r($pass);

    echo "\n\nReverse password check:\n";

    if (ACL::checkHash($_POST['password'], $pass)) echo 'Hash is okay';
    else echo 'Has is not okay';
    echo "\n";

    die();
} else {
    header('Content-Type: text/html');

}

?>
<!DOCTYPE html>
<body>
    <form action="./hashgen.php" method="post">
        <label style="font-size:14px;">Enter password: <input type="password" style="width:200px;font-size:14px;padding:4px;" name="password"></label>
        <button type="submit">Encrypt!</button>
    </form>
</body>