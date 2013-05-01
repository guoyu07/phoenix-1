<?php

/**
 * Edit enrollment page
 *
 * @author  Yectep Studios <info@yectep.hk>
 * @version 30104
 * @package Plume
 */


define('PTP',   '../../private/');
define('PHX_SCRIPT_TYPE',   'HTML');
define('PHX_UX',        true);
define('PHX_STUDENT',   true);
define('PHX_COURSES',   true);


// Include common ignition class
require_once(PTP . 'php/ignition.php');

// We require a staff login for this page
if (!ACL::checkLogin('public')) {
    header('Location: ./login.php?msg=error_nologin');
    exit();
} else {
    $_fam = new FamStu('family', $_SESSION['SSOID']);
    $_fam->getChildren();
    if (sizeof($_fam->children) == 0) {
        header('Location: /account/add_child.php?msg=new_acct');
        exit();
    }

    if (!array_key_exists('STUID', $_SESSION)) {
        header('Location: /account/dashboard.php?msg=no_active');
        exit();
    }

    $_stu = new FamStu('student', $_SESSION['STUID']);
    if ($_stu->data['FamilyID'] !== $_fam->fid) {
        header('Location: /account/dashboard.php?msg=child_exception');
        exit();
    } else {
        $p['sid'] = $_stu->sid;
    }
}

if (!array_key_exists('act', $_GET)) {
    header('Location: /account/error.php?msg=access_violation');
    exit();
}

switch ($_GET['act']) {
    case 'enroll':
        // OKAY?
        $fail = false;
        // Get class
        $class = Courses::getClassById($_GET['cid']);
        if (!$class) {
            header('Location: /account/error.php?msg=access_violation');
            exit();
        }

        // First check to see if course is full
        $enroll_count = Courses::getActiveEnrollmentCount($_GET['cid']);
        if ($enroll_count >= $class['ClassEnrollMax']) {
            $inc = 'account/enroll_error_full';
            $fail = true;
        }

        
        // Age check!
        if (($class['CourseSubj'] == 'ARTS') || ($class['CourseSubj'] == 'LANG') || ($class['CourseSubj'] == 'PHED') || ($class['CourseSubj'] == 'MSCT')) {
            $ageInWeek = Courses::getAgeAtWeek($class['ClassWeek'], $_stu->data['StudentDOB']);
            if ((floor($ageInWeek) > $class['ClassAgeMax']) || ($ageInWeek < $class['ClassAgeMin'])) {
                $inc = 'account/enroll_error_age';
                $p['type'] = ((floor($ageInWeek) > $class['ClassAgeMax']) ? 'old' : 'young');
                $fail = true;
            }
        }

        // Is student already enrolled?
        if ($_stu->isStudentEnrolled($class['ClassWeek'], $class['ClassPeriodBegin']) || $_stu->isStudentEnrolled($class['ClassWeek'], $class['ClassPeriodEnd'])) {
            $inc = 'account/enroll_error_overlap';
            $fail = true;
        }

        // Register if not failed
        if (!$fail) {
            try {
                $stmt = Data::prepare("INSERT INTO `enrollment` (`StudentID`, `ClassID`, `EnrollStatus`, `EnrollCTS`, `EnrollLETS`) VALUES (:stuid, :cid, 'enrolled', NOW(), NOW())");
                $stmt->bindParam('stuid', $_stu->sid, PDO::PARAM_INT);
                $stmt->bindParam('cid', $class['ClassID'], PDO::PARAM_INT);
                $stmt->execute();
                $inc = 'account/enroll_success';
            } catch (PDOException $e) {
                echo UX::grabPage('dev/error', array('pretext' => $e->getMessage()), false);
                exit();
            }
        }

    break;
    case 'enroll_pte':

        $fail = false;

        // Get class
        $class = Courses::getClassById($_GET['cid']);
        if (!$class) {
            header('Location: /account/error.php?msg=access_violation');
            exit();
        }

        // First check to see if course allows PTEs
        $course = Courses::getCourseById($class['CourseID']);
        if ($course['CourseEnforceAge'] == 1) {
            $inc = 'account/enroll_pte_nopte';
            $fail = true;
        }

        if (!$fail) {

            try {
                $stmt = Data::prepare("INSERT INTO `enrollment` (`StudentID`, `ClassID`, `EnrollStatus`, `EnrollCTS`, `EnrollLETS`) VALUES (:stuid, :cid, 'pte_request', NOW(), NOW())");
                $stmt->bindParam('stuid', $_stu->sid, PDO::PARAM_INT);
                $stmt->bindParam('cid', $class['ClassID'], PDO::PARAM_INT);
                $stmt->execute();
                $inc = 'account/enroll_pte_success';
            } catch (PDOException $e) {
                echo UX::grabPage('dev/error', array('pretext' => $e->getMessage()), false);
                exit();
            }

        }

    break;
    case 'drop':

        // Does this actually exist?
        try {
            $stmt = Data::prepare('SELECT e.`StudentID`, c.`CourseTitle` FROM `enrollment` e, `classes`, `courses` c WHERE e.`EnrollID` = :eid and e.`ClassID` = classes.`ClassID` AND c.`CourseID` = classes.`CourseID` LIMIT 0,1');
            $stmt->bindParam('eid', $_GET['eid'], PDO::PARAM_INT);
            $stmt->execute();
            $retval = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            header('Location: /account/error.php?msg=sql');
            exit();
        }

        if ($_stu->sid !== $retval['StudentID']) {
            die($_stu->sid." vs ".$retval['StudentID']);
            header('Location: /account/error.php?msg=access_violation');
            exit();
        } else {
            $p['confirm_string'] = sha1($_stu->data['StudentPrivateKey'].$_GET['eid']);
            $p['eid'] = $_GET['eid'];
            $p['course_title'] = $retval['CourseTitle'];
            $inc = 'account/enroll_drop_confirm';
        }

    break;
    case 'confirm_drop':

        // Only check key and EID
        if (sha1($_stu->data['StudentPrivateKey'].$_GET['eid']) == $_GET['key']) {
            try {
                $stmt = Data::prepare('UPDATE `enrollment` SET `EnrollStatus` = "dropped" WHERE `EnrollID` = :eid AND `StudentID` = :sid LIMIT 1');
                $stmt->bindParam('eid', $_GET['eid'], PDO::PARAM_INT);
                $stmt->bindParam('sid', $_stu->sid, PDO::PARAM_INT);
                $stmt->execute();
            } catch (PDOException $e) {
                header('Location: /account/error.php?msg=sql');
                exit();
            }
            $inc = 'account/enroll_drop_success';
        } else {
            header('Location: /account/error.php?msg=access_violation');
            exit();
        }

    break;
    default:
        header('Location: /account/error.php?msg=access_violation');
        exit();
    break;
}

// Page variables (common)
$p['student_name'] = $_stu->data['StudentNamePreferred'].' '.$_stu->data['StudentNameLast'];

// Include header section
echo UX::makeHead($h, $n, 'common/header_public', 'common/nav_account');

// Page info
echo UX::makeBreadcrumb(array(  'My Family' => "/account/dashboard.php", $_stu->data['StudentNamePreferred'].'\'s Profile &amp; Schedule' => "/account/view_student.php?sid=".$_GET['sid']));
echo UX::grabPage($inc, $p, true);

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

echo UX::grabPage('common/masthead', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>

