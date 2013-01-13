<?php
session_name('SummerSession');
session_start();

header('Content-Type: text/plain');
echo "================== COOKIE DUMP\n";

foreach($_COOKIE as $index => $c) {
    echo "[".$index."] ".$c."\n";
}

echo "\n================== SESSION DUMP\n";
echo "Session ID: ".session_id()."\n";

foreach ($_SESSION as $index => $s) {
    echo "[".$index."] ".$s."\n";
}
exit();

?>