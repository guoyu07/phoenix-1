<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

header('Content-Type: image/png');

require_once('../../private/php/class.qr.php');

if (isset($_GET['type'])) {
    switch ($_GET['type']) {
        case "url":
            $qr = new BarcodeQR();
            $qr->url(((isset($_GET['data'])) ? $_GET['data'] : 'about:blank'));
            $qr->draw(128);
        break;
        case "text":
            $qr = new BarcodeQR();
            $qr->text(((isset($_GET['data'])) ? $_GET['data'] : 'Parse Error: No data given.'));
            $qr->draw(128);
        break;
        default:
            $qr = new BarcodeQR();
            $qr->text('Parse Error: Invalid type specified.');
            $qr->draw(128);
        break;
    }
} else {
    $qr = new BarcodeQR();
    $qr->text('Parse Error: Missing encode type.');
    $qr->draw(128);
}

exit();

?>