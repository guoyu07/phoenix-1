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
define('PHX_MAILER',    true);
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
        case 'add_charge':
            $_laoshi->perms(8,9,10,11,12);
            try {
                $stmt = Data::prepare('INSERT INTO `payments` (`FamilyID`, `PayMethod`, `PayAmount`, `PayCTS`, `PayDesc`, `PayVerified`)
VALUES (:fid, "FrontDesk", :val, NOW(), :desc, 1)');
                $stmt->bindParam('fid', $_REQUEST['fid'], PDO::PARAM_INT);
                $stmt->bindParam('val', $_REQUEST['val'], PDO::PARAM_INT);
                $stmt->bindParam('desc', $_REQUEST['desc'], PDO::PARAM_STR);
                $stmt->execute();
                $result['status'] = 'success';
                $result['code'] = 2000;
                $result['msg'] = '[OK] Charge successfully added to invoice.';
            } catch (PDOException $e) {
                $result['stauts'] = 'failure';
                $result['code'] = 2500;
                $result['msg'] = '[SQL] Error: '.$e->getMessage();
            }
        break;
        case 'update_cell':
            try {
                $stmt = Data::prepare('UPDATE `staff` SET `StaffCell` = :cell WHERE `StaffID` = :sid LIMIT 1');
                $stmt->bindParam('cell', $_REQUEST['number']);
                $stmt->bindParam('sid', $_REQUEST['staff_id']);
                $stmt->execute();
                $result['status'] = 'success';
                $result['code'] = 2000;
                $result['msg'] = '[OK] Successfully added cell.';
            } catch (PDOException $e) {
                $result['stauts'] = 'failure';
                $result['code'] = 2500;
                $result['msg'] = '[SQL] Error: '.$e->getMessage();
            }
        break;
    	case 'add_payment':
            $_laoshi->perms(8,9,10,11,12);
            try {
                $stmt = Data::prepare('INSERT INTO `payments` (`FamilyID`, `PayMethod`, `PayAmount`, `PayCTS`, `PayDesc`, `PayVerified`)
VALUES (:fid, :method, :val, NOW(), :desc, 1)');
                $stmt->bindParam('fid', $_REQUEST['fid'], PDO::PARAM_INT);
                $stmt->bindParam('method', $_REQUEST['paytype'], PDO::PARAM_STR);
                $stmt->bindParam('val', $_REQUEST['val'], PDO::PARAM_INT);
                $stmt->bindParam('desc', $_REQUEST['desc'], PDO::PARAM_STR);
                $stmt->execute();

                $e['desc'] = $_REQUEST['desc'];
                $e['method'] = $_REQUEST['paytype'];
                $e['amount_formatted'] = 'HK$'.number_format((-1)*$_REQUEST['val']);
                $e['cts'] = date(DATETIME_FULL);
                $e['rts'] = date(DATETIME_FULL);

                $fam = FamStu::getFamilyById($_REQUEST['fid']);

                Mailer::send(array('name' => $fam['family']['FamilyName'], 'email' => $fam['family']['FamilyEmail']), '[CIS Summer] Payment Confirmation', UX::grabPage('text_snippets/email_receipt', $e, true));
                
                $result['status'] = 'success';
                $result['code'] = 2000;
                $result['msg'] = '[OK] Payment successfully added to invoice.';
            } catch (PDOException $e) {
                $result['stauts'] = 'failure';
                $result['code'] = 2500;
                $result['msg'] = '[SQL] Error: '.$e->getMessage();
            }
        break;
        case 'archive_application':
    		$_laoshi->perms(6, 7, 8);
    		$stmt = Data::prepare('UPDATE `applications` SET `AppStatus` = "archived" WHERE `AppID` = :appid LIMIT 1');
    		$stmt->bindParam('appid', $_REQUEST['data'], PDO::PARAM_INT);
    		$stmt->execute();

    		$result['status'] = 'success';
    		$result['code'] = 2000;
    		$result['msg'] = '[OK] '.$stmt->rowCount().' rows affected.';
    	break;
        case 'add_class':
            $_laoshi->perms(6,7);

            // Direct insertion
            try {
                $stmt = Data::prepare("INSERT INTO `classes` (`CourseID`, `TeacherID`, `RoomID`, `ClassWeek`, `ClassPeriodBegin`, `ClassPeriodEnd`, `ClassAgeMin`, `ClassAgeMax`, `ClassEnrollMax`, `ClassLastUpdate`, `ClassStatus`) VALUES (:cid, :tid, :room, :week, :pbegin, :pend, :agemin, :agemax, :enrollmax, NOW(), 'closed')");
                $stmt->bindParam('cid', $_REQUEST['cid'], PDO::PARAM_INT);
                $stmt->bindParam('tid', $_REQUEST['tid'], PDO::PARAM_INT);
                $stmt->bindParam('room', $_REQUEST['room'], PDO::PARAM_INT);
                $stmt->bindParam('week', $_REQUEST['week'], PDO::PARAM_INT);
                $stmt->bindParam('pbegin', $_REQUEST['pbegin'], PDO::PARAM_INT);
                $stmt->bindParam('pend', $_REQUEST['pend'], PDO::PARAM_INT);
                $stmt->bindParam('agemin', $_REQUEST['agemin'], PDO::PARAM_INT);
                $stmt->bindParam('agemax', $_REQUEST['agemax'], PDO::PARAM_INT);
                $stmt->bindParam('enrollmax', $_REQUEST['maxenroll'], PDO::PARAM_INT);
                $stmt->execute();
                $result['status'] = 'success';
                $result['code'] = 2200;
                $result['msg'] = 'Class inserted';
            } catch (PDOException $e) {
                $result['status'] = 'failure';
                $result['code'] = 2500;
                $result['msg'] = $e->getMessage();
            }
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
                    $stmt = Data::prepare('UPDATE `courses` SET `'.$_REQUEST['field'].'` = :value WHERE `CourseID` = :courseid LIMIT 1');
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
                    $stmt = Data::prepare('UPDATE `classes` SET `'.$_REQUEST['field'].'` = :value WHERE `ClassID` = :classid LIMIT 1');
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
        case 'cancel_class':
            if (!isset($_REQUEST['classid'])) {
                $result['status'] = 'failure';
                $result['code'] = 2500;
                $result['msg'] = 'Missing parameter.';
            } else {
                try {
                    $stmt = Data::prepare('UPDATE `classes` SET `ClassStatus` = "cancelled" WHERE `ClassID` = :classid LIMIT 1');
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
        case 'pte_accept':
            if (!isset($_REQUEST['eid'])) {
                $result['status'] = 'failure';
                $result['code'] = 2500;
                $result['msg'] = 'Missing parameter.';
            } else {
                try {
                    $stmt = Data::prepare('SELECT * FROM `enrollment` WHERE `EnrollID` = :eid LIMIT 1');
                    $stmt->bindParam('eid', $_REQUEST['eid'], PDO::PARAM_INT);
                    $stmt->execute();
                    $enrollment = $stmt->fetch(PDO::FETCH_ASSOC);

                    $class = Courses::getClassById($enrollment['ClassID']);

                    // Drop other classes
                    $stmt = Data::prepare('SELECT e.* FROM `enrollment` e, `classes` c WHERE c.ClassWeek = :week AND (c.ClassPeriodBegin = :period OR c.ClassPeriodEnd = :period) AND c.ClassID = e.ClassID AND e.StudentID = :stuid  AND e.EnrollStatus = "enrolled"');
                    $stmt->bindParam('week', $class['ClassWeek']);
                    $stmt->bindParam('period', $class['ClassPeriodBegin']);
                    $stmt->bindParam('stuid', $enrollment['StudentID']);
                    $stmt->execute();
                    $period_begin = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach($period_begin as $i => $overlap) {
                        $stmt = Data::prepare('UPDATE `enrollment` SET `EnrollStatus` = "dropped" WHERE `EnrollID` = :eid LIMIT 1');
                        $stmt->bindParam('eid', $overlap['EnrollID']);
                        $stmt->execute();
                    }

                    if ($class['ClassPeriodEnd'] !== $class['ClassPeriodBegin']) {
                        $stmt = Data::prepare('SELECT e.* FROM `enrollment` e, `classes` c WHERE c.ClassWeek = :week AND (c.ClassPeriodBegin = :period OR c.ClassPeriodEnd = :period) AND c.ClassID = e.ClassID AND e.StudentID = :stuid  AND e.EnrollStatus = "enrolled"');
                        $stmt->bindParam('week', $class['ClassWeek']);
                        $stmt->bindParam('period', $class['ClassPeriodEnd']);
                        $stmt->bindParam('stuid', $enrollment['StudentID']);
                        $stmt->execute();
                        $period_end = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        foreach($period_end as $i => $overlap) {
                            $stmt = Data::prepare('UPDATE `enrollment` SET `EnrollStatus` = "dropped" WHERE `EnrollID` = :eid LIMIT 1');
                            $stmt->bindParam('eid', $overlap['EnrollID']);
                            $stmt->execute();
                        }
                    }

                    // Now enroll
                    $stmt = Data::prepare('UPDATE `enrollment` SET `EnrollStatus` = "enrolled" WHERE `EnrollID` = :eid LIMIT 1');
                    $stmt->bindParam('eid', $_REQUEST['eid'], PDO::PARAM_INT);
                    $stmt->execute();

                    // Get email
                    $stmt = Data::prepare('SELECT o.ObjEmail, f.FamilyName FROM sso_objects o, students s, families f WHERE s.FamilyID = f.FamilyID AND f.ObjID = o.ObjID AND s.StudentID = :sid');
                    $stmt->bindParam('sid', $enrollment['StudentID']);
                    $stmt->execute();

                    $email = $stmt->fetch(PDO::FETCH_ASSOC);

                    Mailer::send(array('email' => $email['ObjEmail'], 'name' => $email['FamilyName']), '[CIS Summer] PTE Accepted', urldecode($_REQUEST['email']));

                    $result['status'] = 'success';
                    $result['code'] = 2400;
                    $result['msg'] = 'PTE accept was successful.';
                } catch (PDOException $e) {
                    $result['status'] = 'failure';
                    $result['code'] = 2500;
                    $result['msg'] = $e->getMessage();
                }

            }
        break;
        case 'pte_deny':
            if (!isset($_REQUEST['eid'])) {
                $result['status'] = 'failure';
                $result['code'] = 2500;
                $result['msg'] = 'Missing parameter.';
            } else {
                try {
                    $stmt = Data::prepare('SELECT * FROM `enrollment` WHERE `EnrollID` = :eid LIMIT 1');
                    $stmt->bindParam('eid', $_REQUEST['eid'], PDO::PARAM_INT);
                    $stmt->execute();
                    $enrollment = $stmt->fetch(PDO::FETCH_ASSOC);

                    // Mark denied
                    $stmt = Data::prepare('UPDATE `enrollment` SET `EnrollStatus` = "pte_denied" WHERE `EnrollID` = :eid LIMIT 1');
                    $stmt->bindParam('eid', $_REQUEST['eid'], PDO::PARAM_INT);
                    $stmt->execute();

                    // Get email
                    $stmt = Data::prepare('SELECT o.ObjEmail, f.FamilyName FROM sso_objects o, students s, families f WHERE s.FamilyID = f.FamilyID AND f.ObjID = o.ObjID AND s.StudentID = :sid');
                    $stmt->bindParam('sid', $enrollment['StudentID']);
                    $stmt->execute();

                    $email = $stmt->fetch(PDO::FETCH_ASSOC);

                    Mailer::send(array('email' => $email['ObjEmail'], 'name' => $email['FamilyName']), '[CIS Summer] PTE Unsuccessful', urldecode($_REQUEST['email']));

                    $result['status'] = 'success';
                    $result['code'] = 2400;
                    $result['msg'] = 'Deny request was successful.';
                } catch (PDOException $e) {
                    $result['status'] = 'failure';
                    $result['code'] = 2500;
                    $result['msg'] = $e->getMessage();
                }

            }
        break;
        case 'mark_student_verified':
            if (!isset($_REQUEST['sid'])) {
                $result['status'] = 'failure';
                $result['code'] = 2500;
                $result['msg'] = 'Missing parameter.';
            } else {
                try {
                    $stmt = Data::prepare('SELECT * FROM `students` WHERE `StudentID` = :sid LIMIT 1');
                    $stmt->bindParam('sid', $_REQUEST['sid'], PDO::PARAM_INT);
                    $stmt->execute();
                    $stustatus = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($stustatus['StudentIC'] == '1') {
                        // Mark not approved
                        $stmt = Data::prepare('UPDATE `students` SET `StudentIC` = "0" WHERE `StudentID` = :sid LIMIT 1');
                        $stmt->bindParam('sid', $_REQUEST['sid'], PDO::PARAM_INT);
                        $stmt->execute();
                    } else {
                        // Mark approved
                        $stmt = Data::prepare('UPDATE `students` SET `StudentIC` = "1" WHERE `StudentID` = :sid LIMIT 1');
                        $stmt->bindParam('sid', $_REQUEST['sid'], PDO::PARAM_INT);
                        $stmt->execute();
                    }
                    $result['status'] = 'success';
                    $result['code'] = 2400;
                    $result['msg'] = 'Deny request was successful.';
                } catch (PDOException $e) {
                    $result['status'] = 'failure';
                    $result['code'] = 2500;
                    $result['msg'] = $e->getMessage();
                }

            }
        break;
        case 'force_submit':
            if (!isset($_REQUEST['sid'])) {
                $result['status'] = 'failure';
                $result['code'] = 2500;
                $result['msg'] = 'Missing parameter.';
            } else {
                try {
                    $stmt = Data::prepare('UPDATE `students` SET `StudentSubmitted` = 1, `StudentSubmitTS` = NOW() WHERE `StudentID` = :sid LIMIT 1');
                    $stmt->bindParam('sid', $_REQUEST['sid'], PDO::PARAM_INT);
                    $stmt->execute();
                    $done = $stmt->fetch(PDO::FETCH_ASSOC);

                    $result['status'] = 'success';
                    $result['code'] = 2400;
                    $result['msg'] = 'Deny request was successful.';
                } catch (PDOException $e) {
                    $result['status'] = 'failure';
                    $result['code'] = 2500;
                    $result['msg'] = $e->getMessage();
                }

            }
        break;
        case 'cancel_class_email':
            if (!isset($_REQUEST['cid'])) {
                $result['status'] = 'failure';
                $result['code'] = 2500;
                $result['msg'] = 'Missing parameter.';
            } else {
                try {
                    $stmt = Data::prepare('select sso.ObjEmail as email, f.FamilyName as name from enrollment e, students s, families f, sso_objects sso where sso.ObjID = f.ObjID and f.FamilyID = s.FamilyID and s.StudentID = e.StudentID and e.ClassID = :cid and e.EnrollStatus = "enrolled"');
                    $stmt->bindParam('cid', $_REQUEST['cid'], PDO::PARAM_INT);
                    $stmt->execute();
                    $parent_list = $stmt->fetch(PDO::FETCH_ASSOC);

                    Mailer::send($parent_list, '[CIS Summer] Class Cancelation Notification', urldecode($_REQUEST['email']));

                    $stmt = Data::prepare('UPDATE `enrollment` SET EnrollStatus = "dropped" WHERE ClassID = :cid');
                    $stmt->bindParam('cid', $_REQUEST['cid'], PDO::PARAM_INT);
                    $stmt->execute();

                    $result['status'] = 'success';
                    $result['code'] = 2400;
                    $result['msg'] = 'Class canceled';
                } catch (PDOException $e) {
                    $result['status'] = 'failure';
                    $result['code'] = 2500;
                    $result['msg'] = $e->getMessage();
                }

            }
        break;
        case 'registration':
            try {
                $stmt = Data::prepare('INSERT INTO `registration` (`ClassID`, `StudentID`, `RegStatus`, `RegDate`, `RegLATS`) VALUES (:cid, :sid, :status, NOW(), NOW());');
                $stmt->bindParam('status', $_REQUEST['status']);
                $stmt->bindParam('cid', $_REQUEST['cid']);
                $stmt->bindParam('sid', $_REQUEST['sid']);
                $stmt->execute();
                $result['status'] = 'success';
                $result['code'] = 2400;
                $result['msg'] = 'Marked';
            } catch (PDOException $e) {
                $result['status'] = 'failure';
                $result['code'] = 2500;
                $result['msg'] = $e->getMessage();
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

