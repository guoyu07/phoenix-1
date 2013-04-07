<?php

/**
 * Remote procedure call JSON/AJAXified script
 *
 * @author	Yectep Studios <info@yectep.hk>
 * @version	20923
 * @package Plume
 */


define('PTP',   '../private/');
define('PHX_MAILER',        true);
define('PHX_UX',            true);
define('PHX_COURSES',       true);
define('PHX_SCRIPT_TYPE',   'JSON');


// Include common ignition class
require_once(PTP . 'php/ignition.php');

// If there's no security key we generate one
$skip = false;
if (!isset($_REQUEST['uid']) || !isset($_REQUEST['key'])) {
    $output["uid"] = uniqid();
    $output["key"] = sha1(session_id().$output["uid"].$_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT']);
} else {
    // Check security key
    if (isset($_REQUEST['key']) && isset($_REQUEST['uid']) && (sha1(session_id().$_REQUEST['uid'].$_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT']) == $_REQUEST['key'])) {
        // Alright
        $skip = false;
    } else {
        // Failed validation
        $output["responseCode"] = 2403;
        $output["error"] = "Request key failed to validate. Please reinitiate request.";
        $skip = true;
    }
}

if ($skip) die(json_encode($output));


if (isset($_REQUEST['method'])) {

    switch($_REQUEST['method']) {
        case 'loadCourses':
            // Subject filter
            if (array_key_exists('subjects', $_REQUEST) && (sizeof($_REQUEST['subjects']) > 0)) {
                $subj = "'".implode("','", explode(',', $_REQUEST['subjects']))."'";
            } else {
                $subj = "'ARTS','MSCT','PHED','LANG'";
            }

            // Age filter
            if (array_key_exists('age', $_REQUEST)) {
                $agemin = (int) $_REQUEST['age'];
                $agemax = (int) $_REQUEST['age'];
            } else {
                $agemin = 18;
                $agemax = 0;
            }

            // Title
            if (array_key_exists('title', $_REQUEST)) {
                $title = "'%".$_REQUEST['title']."%'";
            } else {
                $title = "'%%'";
            }

            // Build query
            $stmt = Data::prepare("SELECT c.CourseID as cid, c.CourseSubj as `subject`, c.CourseTitle as title, c.CourseSynop as synopsis, MIN(l.ClassAgeMin) as minage, MAX(l.ClassAgeMax) as maxage, (SELECT staff.StaffName from staff WHERE staff.StaffID = c.TeacherLead) as lead_instructor FROM `courses` c
    INNER JOIN `classes` l USING(CourseID)
    WHERE l.ClassAgeMin <= :agemin AND l.ClassAgeMax >= :agemax AND c.CourseTitle LIKE ".$title." AND c.CourseSubj IN (".$subj.")
    GROUP BY c.CourseID
    ORDER BY c.CourseTitle ASC");
            $stmt->bindParam('agemin', $agemin);
            $stmt->bindParam('agemax', $agemax);
            $stmt->execute();
            $course_data = $stmt->fetchAll(PDO::FETCH_ASSOC);


            $output["subjects"] = $subj;
            $output["age"] = $agemin."-".$agemax;
            $output["data"] = $course_data;
            break;
        case 'checkEmail':
        
            // Email check
            if (isset($_REQUEST['data'])) {
                $output["responseCode"] = 2200;
                if (ACL::checkEmail($_REQUEST['data'])) $output["response"] = true;
                else $output["response"] = false;
            } else {
                $output["responseCode"] = 2400;
                $output["error"] = "Missing data element (email).";
            }
            break;
            
        case 'resetAccount':
        
            // Reset password request
            if (isset($_REQUEST['data'])) {
                if (ACL::checkEmail($_REQUEST['data'])) {
                    // Email exists, send link to reset
                    $stmt = Data::prepare('SELECT `FamilySalt` FROM `families` WHERE `FamilyEmail` = :email');
                    $stmt->bindParam('email', $_REQUEST['data']);
                    $stmt->execute();
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    $body = UX::grabPage('text_snippets/email_change', array('saltsha' => sha1($result['FamilySalt']), 'email' => $_REQUEST['data']), true);
                    Mailer::send(array('email' => $_REQUEST['data']), 'Account reset details', $body);
                    $output["responseCode"] = 2200;
                } else {
                    $output["responseCode"] = 2404;
                    $output["error"] = "There is no such account registered under this email address. Have you registered yet?";
                }
            } else {
                $output["responseCode"] = 2400;
                $output["error"] = "Sorry, you need to enter an email before requesting a password reset link.";
            }
            break;
            
        default:
        
            // No method specified
            $output["responseCode"] = 2404;
            $output["error"] = "The method specified does not exist or has been deprecated.";
        break;
    }

} else {

    $output["responseCode"] = 2401;
    $output["error"] = "No method was specified in the request";
    
}

die(json_encode($output));
?>