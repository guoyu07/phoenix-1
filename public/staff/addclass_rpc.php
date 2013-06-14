<?php

/**
 * Staff dashboard
 *
 * @author  Yectep Studios <info@yectep.hk>
 * @version 30104
 * @package Plume
 * @subpackage Staff
 */


define('PTP',   '../../private/');
define('PHX_SCRIPT_TYPE',   'JSON');
define('PHX_COURSES',   true);
define('PHX_LAOSHI',    true);


// Include common ignition class
require_once(PTP . 'php/ignition.php');

// We require a staff login for this page
if (!ACL::checkLogin('staff')) {
    $result['status'] = 'failure';
    $result['code'] = 2400;
    $result['msg'] = 'Login not provided';
} else {
    $_laoshi = new Laoshi($_SESSION['SSOID']);

    if (!$_GET['str'] || !$_GET['sid']) {
        $result['status'] = 'failure';
        $result['code'] = 2400;
        $result['msg'] = 'No add class string provided';
    } else {
        // Explode it!
        $str = explode(',', $_GET['str']);
        $result['return'] = $str;
        $cid = substr($str[0], 4);
        $stmt = Data::prepare('SELECT co.*, c.* FROM courses co, classes c WHERE c.CourseID = co.CourseID AND c.CourseID = :courseid AND c.ClassWeek = :week AND c.ClassPeriodBegin = :period LIMIT 1');
        $stmt->bindParam('courseid', $cid);
        $stmt->bindParam('week', $str[1]);
        $stmt->bindParam('period', $str[2]);
        $stmt->execute();
        $result['data_raw'] = $stmt->fetch(PDO::FETCH_ASSOC);

        if (sizeof($result['data_raw']) < 1) {
            $result['status'] = 'failure';
            $result['code'] = 5400;
            $result['msg'] = 'Class does not exist!';
        } else {

            // Check enrollments
            $stmt = Data::prepare('SELECT COUNT(EnrollID) as enrolled FROM enrollment WHERE ClassID = :classid AND EnrollStatus = "enrolled"');
            $stmt->bindParam('classid', $result['data_raw']['ClassID']);
            $stmt->execute();
            $enrolled = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($enrolled['enrolled'] >= $result['data_raw']['ClassEnrollMax']) {
                $result['full'] = true;
            } else {
                $result['full'] = false;
            }

            // Enroll the kid!
            $stmt = Data::prepare('INSERT INTO `enrollment` (`StudentID`, `ClassID`, `EnrollStatus`, `EnrollCTS`, `EnrollLETS`) VALUES (:stuid, :classid, "enrolled", NOW(), NOW())');
            $stmt->bindParam('stuid', $_GET['sid'], PDO::PARAM_INT);
            $stmt->bindParam('classid', $result['data_raw']['ClassID'], PDO::PARAM_INT);
            $stmt->execute();

            $result['status'] = 'success';
        }

    }
}

// Send out json data
echo json_encode($result);
exit();

?>

