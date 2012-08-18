<?php
switch ($_SERVER['QUERY_STRING']) {
    case "view_php":
        phpinfo();
        break;
    case "view_snmp":
        header("Location: http://www.ninearches.net/cacti");
        exit();
        break;
    default:
        require('./apc.php');
        break;
}       
?>