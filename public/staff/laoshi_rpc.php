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
define('PHX_COURSES', 	true);
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

    switch($_REQUEST['method']) {
    	case 'archive_application':
    		$_laoshi->perms(6, 7, 8);
    		$stmt = Data::prepare('UPDATE `applications` SET `AppStatus` = "archived" WHERE `AppID` = :appid LIMIT 1');
    		$stmt->bindParam('appid', $_REQUEST['data'], PDO::PARAM_INT);
    		$stmt->execute();

    		$result['status'] = 'success';
    		$result['code'] = 2000;
    		$result['msg'] = '[OK] '.$stmt->rowCount().' rows affected.';
    	break;
        case 'add_course':
            $_laoshi->perms(6, 7);
            $receive = json_decode($_REQUEST['data'], true);
            $result = $receive;
            // Does teacher exist?
            $teacherObjId = ACL::addTeacherSafe($receive['teachers']['lead_email'], $receive['teachers']['lead_name']);
            $result['teacher_objid'] = $teacherObjId;
            
            try {
                // Create the course
                $stmt = Data::prepare('INSERT INTO `courses` (`CourseSubj`, `TeacherLead`, `CourseTitle`, `CourseSynop`, `CourseDescription`, `CourseLOI`, `CourseDifficulty`, `CoursePrereqs`, `CourseOutcomes`, `CourseComputers`, `CourseInstruments`, `CourseActivity`, `CourseStudentEqp`, `CourseFeeAddon`, `CourseRemarks`, `CourseIC`, `CourseEnforceAge`, `CourseEnforceEnroll`, `CourseFlagsPublic`) VALUES (:subject, :tid, :title, :synop, :descr, :lang, :diff, :prereqs, :outcomes, :computers, :instruments, :activity, "", :addon, :remarks, "", :enforceage, 1, 0)');
                $stmt->bindParam('subject', strtoupper($receive['course_data']['subject']), PDO::PARAM_STR);
                $stmt->bindParam('tid', $teacherObjId, PDO::PARAM_INT);
                $stmt->bindParam('title', $receive['course_data']['name'], PDO::PARAM_STR);
                $stmt->bindParam('synop', $receive['course_data']['synopsis'], PDO::PARAM_STR);
                $stmt->bindParam('descr', $receive['course_data']['description'], PDO::PARAM_STR);
                $stmt->bindParam('lang', strtolower($receive['course_data']['lang']), PDO::PARAM_STR);
                $stmt->bindParam('diff', $receive['course_data']['difficulty'], PDO::PARAM_STR);
                $stmt->bindParam('prereqs', $receive['course_data']['prereqs'], PDO::PARAM_STR);
                $stmt->bindParam('outcomes', $receive['course_data']['outcomes'], PDO::PARAM_STR);
                $stmt->bindParam('computers', $receive['flags']['use_computers'], PDO::PARAM_INT);
                $stmt->bindParam('activity', $receive['flags']['use_activity'], PDO::PARAM_INT);
                $stmt->bindParam('instruments', $receive['flags']['use_instruments'], PDO::PARAM_INT);
                $stmt->bindParam('enforceage', $receive['flags']['enforce_age'], PDO::PARAM_INT);
                $stmt->bindParam('addon', $receive['course_data']['extra_fees'], PDO::PARAM_INT);
                $stmt->bindParam('remarks', $receive['course_data']['ic'], PDO::PARAM_STR);
                $stmt->execute();

                // Get it back
                $stmt = Data::prepare('SELECT `CourseID` FROM `courses` WHERE `CourseTitle` = :name LIMIT 1');
                $stmt->bindParam('name', $receive['course_data']['name']);
                $stmt->execute();
                $rowDat = $stmt->fetch(PDO::FETCH_ASSOC);
                $courseId = $rowDat['CourseID'];

                // Classes
                foreach ($receive['class_data'] as $class) {
                    // Solve for begin and end period
                    switch ($class['period']) {
                        case "A":
                            // Academic Session A
                            $period_begin = 'A';
                            $period_end = 'A';
                        break;
                        case "B":
                            // Academic Session B
                            $period_begin = 'B';
                            $period_end = 'B';
                        break;
                        case "C":
                            // Academic Session C
                            $period_begin = 'C';
                            $period_end = 'C';
                        break;
                        case "1":
                            // SP Period 1 single
                            $period_begin = '1';
                            $period_end = '1';
                        break;
                        case "2":
                            // SP Period 2 single
                            $period_begin = '2';
                            $period_end = '2';
                        break;
                        case "3":
                            // SP Period 3 single
                            $period_begin = '3';
                            $period_end = '3';
                        break;
                        case "4":
                            // SP Period 4 single
                            $period_begin = '4';
                            $period_end = '4';
                        break;
                        case "8":
                            // SP Period 1-2 double
                            $period_begin = '1';
                            $period_end = '2';
                        break;
                        case "9":
                            // SP Period 3-4 double
                            $period_begin = '3';
                            $period_end = '4';
                        break;
                        default:
                            // Unknown
                            $period_begin = '0';
                            $period_end = '0';
                        break;
                    }

                    // Ages
                    $ages = explode('-', $class['ages']);

                    $stmt = Data::prepare('INSERT INTO `classes` (`CourseID`, `TeacherID`, `RoomID`, `ClassWeek`, `ClassPeriodBegin`, `ClassPeriodEnd`, `ClassAgeMin`, `ClassAgeMax`, `ClassEnrollMax`, `ClassLastUpdate`) VALUES(:courseid, :tid, :room, :week, :period_start, :period_end, :age_low, :age_high, :maxenroll, NOW())');
                    $stmt->bindParam('courseid', $courseId);
                    $stmt->bindParam('tid', $teacherObjId);
                    $stmt->bindParam('room', $class['pref_room']);
                    $stmt->bindParam('week', $class['week']);
                    $stmt->bindParam('period_start', $period_begin);
                    $stmt->bindParam('period_end', $period_end);
                    $stmt->bindParam('age_low', $ages[0]);
                    $stmt->bindParam('age_high', $ages[1]);
                    $stmt->bindParam('maxenroll', $class['max_students']);
                    $stmt->execute();
                }

                // Update application
                $stmt = Data::prepare('UPDATE `applications` SET `AppStatus` = "inserted" WHERE `AppID` = :appid LIMIT 1');
                $stmt->bindParam('appid', $receive['app_id'], PDO::PARAM_INT);
                $stmt->execute();

                $result['status'] = 'success';
                $result['code'] = 2400;
                $result['msg'] = 'Course '.strtoupper($receive['course_data']['subject']).str_pad($courseId, 3, '0', STR_PAD_LEFT).' created and '.sizeof($receive['class_data']).' class(es) added to database. All are hidden from public view for the time being. Application has been marked as completed and archived.';
            } catch (PDOException $e) {
                $result['status'] = 'failure';
                $result['code'] = 2500;
                $result['msg'] = $e->getMessage();
            }
        break;
        case 'update_course':
            if (!isset($_REQUEST['value']) || !isset($_REQUEST['field']) || !isset($_REQUEST['courseid'])) {
                $result['status'] = 'failure';
                $result['code'] = 2500;
                $result['msg'] = 'Missing parameter.';
            } else {
                $value = urldecode($_REQUEST['value']);
                try {
                    $stmt = Data::prepare('UPDATE `courses` SET `'.$_REQUEST['field'].'` = :value WHERE `CourseID` = :courseid');
                    $stmt->bindParam('value', $value, PDO::PARAM_STR);
                    $stmt->bindParam('courseid', $_REQUEST['courseid'], PDO::PARAM_INT);
                    $stmt->execute();

                    $result['status'] = 'success';
                    $result['code'] = 2400;
                    $result['msg'] = 'Update was successful.';
                } catch (PDOException $e) {
                    $result['status'] = 'failure';
                    $result['code'] = 2500;
                    $result['msg'] = $e->getMessage();
                }
            }
        break;
        case 'update_class':
            if (!isset($_REQUEST['value']) || !isset($_REQUEST['field']) || !isset($_REQUEST['classid'])) {
                $result['status'] = 'failure';
                $result['code'] = 2500;
                $result['msg'] = 'Missing parameter.';
            } else {
                $value = urldecode($_REQUEST['value']);
                try {
                    $stmt = Data::prepare('UPDATE `classes` SET `'.$_REQUEST['field'].'` = :value WHERE `ClassID` = :classid');
                    $stmt->bindParam('value', $value, PDO::PARAM_STR);
                    $stmt->bindParam('classid', $_REQUEST['classid'], PDO::PARAM_INT);
                    $stmt->execute();

                    $result['status'] = 'success';
                    $result['code'] = 2400;
                    $result['msg'] = 'Update was successful.';
                } catch (PDOException $e) {
                    $result['status'] = 'failure';
                    $result['code'] = 2500;
                    $result['msg'] = $e->getMessage();
                }
            }
        break;
    	default:
			$result['status'] = 'failure';
			$result['code'] = 2404;
			$result['msg'] = 'Invalid method';
		break;
    }
}

// Send out json data
echo json_encode($result);
exit();

?>

