<?php

/**
 * Family dashboard
 *
 * @author  Yectep Studios <info@yectep.hk>
 * @version 30104
 * @package Plume
 */


define('PTP',   '../../private/');
define('PHX_SCRIPT_TYPE',   'JSON');
define('PHX_UX',        true);
define('PHX_STUDENT',   true);
define('PHX_COURSES',   true);
define('PHX_FINANCES',  true);


// Include common ignition class
require_once(PTP . 'php/ignition.php');

// We require a staff login for this page
if (!ACL::checkLogin('public')) {
    $result['result'] = 'error';
    $result['msg'] = 'No POST data found';
} else {
    $_fam = new FamStu('family', $_SESSION['SSOID']);

    if (isset($_POST)) {

        if (array_key_exists('bank_name', $_POST) && array_key_exists('cheque_num', $_POST) && array_key_exists('value', $_POST)) {
            $description = $_POST['bank_name'].'/ Cheque #'.$_POST['cheque_num'];
            $value = -1*abs(round($_POST['value']));
            try {
                $stmt = Data::prepare('INSERT INTO `payments` (`FamilyID`, `PayMethod`, `PayAmount`, `PayCTS`, `PayDesc`, `PayVerified`) VALUES (:famid, "Cheque", :val, NOW(), :descr, 0)');
                $stmt->bindParam('famid', $_fam->data['FamilyID']);
                $stmt->bindParam('val', $value, PDO::PARAM_INT);
                $stmt->bindParam('descr', $description, PDO::PARAM_STR);
                $stmt->execute();

                $stmt = Data::prepare('SELECT PayID, FamilyID from `payments` WHERE `FamilyID` = :famid AND `PayDesc` = :descr LIMIT 1');
                $stmt->bindParam('famid', $_fam->data['FamilyID']);
                $stmt->bindParam('descr', $description, PDO::PARAM_STR);
                $stmt->execute();
                $info = $stmt->fetch(PDO::FETCH_ASSOC);

                $result['result'] = 'success';
                $result['msg'] = '/print_payment.php?pid='.$info['PayID'].'&c='.sha1($info['PayID'].':'.$info['FamilyID'].':cis_summer');

            } catch (PDOException $e) {
                $result['result'] = 'error';
                $result['msg'] = 'MySQL Error';
            }

        } else {
            $result['result'] = 'error';
            $result['msg'] = 'Missing parameter(s)';
        }


    } else {
        $result['result'] = 'error';
        $result['msg'] = 'No POST data found';
    }

}


echo json_encode($result);

?>

