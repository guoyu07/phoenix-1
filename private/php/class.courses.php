<?php

/**
 * The Courses class contains features and methods
 * to load multiple course data, teacher data and
 * class data.
 *
 * @author      Yectep Studios <info@yectep.hk>
 * @version     20930
 * @package     Phoenix
 *
 */
class Courses {

    /**
     * Returns an array of rooms available for use based on the rooms table.
     * @return mixed
     */
    static public function getRoomList() {

        $stmt = Data::query('SELECT * FROM `rooms` ORDER BY `RoomID` ASC, `RoomName` ASC');
        $intData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $retData = array();

        foreach($intData as $room) {
            $retData[$room["RoomID"]] = array(  "name"      => $room["RoomName"],
                                                "block"   => $room["RoomComment"]);
        }

        return $retData;

    }

    /**
     * Gets all applications based on a certain status
     */
    static public function getApps($status = 'submitted') {
        try {
            $stmt = Data::prepare('SELECT * FROM `applications` WHERE `AppStatus` = :status ORDER BY `AppLETS` DESC');
            $stmt->bindParam('status', $status);
            $stmt->execute();
            $courseRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            Common::throwNiceDataException($e);
        }

        $courses = array();

        foreach($courseRaw as $app) {
            $infoJson = json_decode($app['AppFormJSON'], true);
            $classJson = json_decode($app['AppCourseJSON'], true);

            $student_count = 0;

            foreach($classJson as $class) {
                $student_count += $class['max_students'];
            }

            array_push($courses, array('app_id' => $app['AppID'],
                'program' => $infoJson['program'],
                'subject' => (($infoJson['program'] == 'SP') ? $infoJson['sp_subject'] : $infoJson['ap_subject']),
                'teacher_name' => $infoJson['teacher_name'],
                'teacher_email' => $infoJson['teacher_email'],
                'course_name' => $infoJson['course_name'],
                'count' => $student_count,
                'submitted' => date(DATETIME_SHORT, strtotime($app['AppLETS']))
            ));
        }

        return $courses;
    }

    /**
     * Gets and returns a data array (similar to load app) for a particular submitted application based on ID
     * @param   int $id
     */
    static public function getAppDataById($id) {
        try {
            $stmt = Data::prepare('SELECT * FROM `applications` WHERE `AppID` = :id AND `AppStatus` IN ("submitted", "inserted") LIMIT 1');
            $stmt->bindParam('id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            Common::throwNiceDataException($e);

        }

        if (!$data) {
            return false;
        }

        $ret['course_info'] = json_decode($data['AppFormJSON'], true);
        $ret['class_info'] = json_decode($data['AppCourseJSON'], true);
        $ret['contact_email'] = $data['AppEmail'];
        $ret['status'] = $data['AppStatus'];
        $ret['timestamps'] = array('created' => date(DATETIME_SHORT, strtotime($data['AppCTS'])), 'last_edit' => date(DATETIME_SHORT, strtotime($data['AppLETS'])));

        return $ret;
    }

    /**
     * Get name of subject
     * @param   string $code
     */
    static public function getSubjectName($code) {
        switch ($code) {
            case 'ARTS':
                return 'The Arts';
            break;
            case 'LANG':
                return 'Languages';
            break;
            case 'MSCT':
                return 'Maths, Science &amp; Technology';
            break;
            case 'PHED':
                return 'Physical Education';
            break;
            case 'IBTC':
                return 'IBDP Taster Course';
            break;
            case 'IBRV':
                return 'IBDP Review';
            break;
            case 'SATS':
                return 'SAT Subject Test Prep';
            break;
            case 'CISO':
                return 'CIS 101';
            break;
            case 'PATH':
                return 'Career Pathways';
            break;
        }
    }

    /**
     * Get list of courses
     */
    static public function getCourseList() {
        if (func_num_args() > 0)
            $subjects = func_get_args();
        else
            $subjects = array('ARTS', 'LANG', 'PHED', 'MSCT', 'IBRV', 'IBTC', 'CISO', 'PATH', 'SATS');
        $subjectText = '"'.implode('", "', $subjects).'"';
        
        try {
            $stmt = Data::query('SELECT * FROM `courses` WHERE `CourseSubj` IN ('.$subjectText.') ORDER BY `CourseSubj` ASC, `CourseID` ASC');
        } catch (PDOException $e) {
            Common::throwNiceDataException($e);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get information on a single course based on ID
     * @param   int     $cid    Course ID number to lookup data for
     * @return  mixed
     */
    static public function getCourseById($cid) {
        $stmt = Data::prepare('SELECT * FROM `courses` WHERE `CourseID` = :cid LIMIT 1');
        $stmt->bindParam('cid', $cid, PDO::PARAM_INT);
        $stmt->execute();
        $info = $stmt->fetch(PDO::FETCH_ASSOC);

        if (sizeof($info) > 1) {
            return $info;
        } else {
            return false;
        }
    }

    /**
     * Get information on a single class based on ID
     * @param   int     $cid    Class ID number to lookup data for
     * @return  mixed
     */
    static public function getClassById($cid) {
        $stmt = Data::prepare('SELECT `classes`.*, `courses`.`CourseTitle`, `courses`.`CourseSubj` FROM `classes`, `courses` WHERE `classes`.`ClassID` = :cid AND `classes`.`CourseID` = `courses`.`CourseID` LIMIT 1');
        $stmt->bindParam('cid', $cid, PDO::PARAM_INT);
        $stmt->execute();
        $info = $stmt->fetch(PDO::FETCH_ASSOC);

        if (sizeof($info) > 1) {
            return $info;
        } else {
            return false;
        }
    }

    /**
     * Get enrollments for a single class
     * @param   int $cid    Class ID
     * @return  mixed
     */
    static public function getClassEnrollment($cid) {
        $stmt = Data::prepare('SELECT s.`StudentID`, CONCAT(s.`StudentNamePreferred`, " ", s.`StudentNameLast`) FROM `students` s, `enrollment` e WHERE e.`ClassID` = :cid AND e.`EnrollStatus` = "enrolled" AND e.`StudentID` = s.`StudentID`');
        $stmt->bindParam('cid', $cid, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get course classes
     */
    static public function getClassesOfCourseById($id) {
        try {
            $stmt = Data::prepare('SELECT `classes`.*, (SELECT count(`enrollment`.`EnrollID`) FROM `enrollment` WHERE `enrollment`.`ClassID` = `classes`.`ClassID` and `enrollment`.`EnrollStatus` = "enrolled") as `EnrollCount` FROM `classes` where `classes`.`CourseID` = :cid ORDER BY `classes`.`ClassWeek` ASC, `classes`.`ClassPeriodBegin` ASC');
            $stmt->bindParam('cid', $id, PDO::PARAM_INT);
            $stmt->execute();
            $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($classes as $index => $class) {
                $classes[$index]['ClassHtmlStatus'] = self::getHtmlStatus($class['ClassStatus']);
            }
            return $classes;
        } catch (PDOException $e) {
            Common::throwNiceDataException($e);
        }
    }

    /**
     * Gets the age of the student given the week
     */
    static public function getAgeAtWeek($week, $dob) {
        $dobDo = new DateTime($dob);

        switch($week) {
            case 1:
                $weekDo = new DateTime('2013-06-24 12:00:00');
            break;
            case 2:
                $weekDo = new DateTime('2013-07-02 12:00:00');
            break;
            case 3:
                $weekDo = new DateTime('2013-07-08 12:00:00');
            break;
            case 4:
                $weekDo = new DateTime('2013-07-15 12:00:00');
            break;
        }

        return (float) round(($weekDo->diff($dobDo)->y + ($weekDo->diff($dobDo)->m)/12), 1);
    }

    /**
     * Get teacher info (email and display name)
     */
    static public function getTeacherById($id) {
        $stmt = Data::prepare('SELECT `staff`.`StaffID` as "TeacherID", `staff`.`StaffName` as "TeacherName", `sso_objects`.`ObjEmail` as "TeacherEmail" FROM `staff`, `sso_objects` WHERE `sso_objects`.`ObjID` = `staff`.`ObjID` AND `staff`.`StaffID` = :sid');
        $stmt->bindParam('sid', $id, PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (sizeof($res) == 1) {
            return $res[0];
        } else {
            return false;
        }
    }

    /**
     * Get teacher info by email. Creates it and returns ID if necessary
     */
    static public function getTeacher($email, $name) {
        // First check whether the teacher exists, if not create SSO and staff records
        
    }

    /**
     * Gets active (enrolled) count for a given class ID
     */
    static public function getActiveEnrollmentCount($cid) {
        try {
            $stmt = Data::prepare("SELECT COUNT(EnrollID) as `count` FROM `enrollment` WHERE `ClassID` = :cid AND `EnrollStatus` = 'enrolled'");
            $stmt->bindParam('cid', $cid, PDO::PARAM_INT);
            $stmt->execute();
            $rv = $stmt->fetch(PDO::FETCH_ASSOC);
            return $rv['count'];
        } catch (PDOException $e) {
            Common::throwNiceDataException($e);
        }
    }

    /**
     * Returns an HTML-friendly status based on class status
     */
    static public function getHtmlStatus($status = 'unknown') {
        switch ($status) {
            case 'active':
                return '<img src="/assets/icons/tick-white.png" /> Open';
            break;
            case 'full':
                return '<img src="/assets/icons/cross.png" /> <strong class="red">Class full</strong>';
            break;
            case 'closed':
                return '<img src="/assets/icons/exclamation.png" /> <span class="red">Closed</span>';
            break;
            case 'cancelled':
                return '<img src="/assets/icons/exclamation.png" /> <span class="muted">Cancelled</span>';
            break;
            default:
                return '<img src="/assets/icons/exclamation.png" /> <span class="muted">Status unknown</span>';
            break;
        }
    }

    /**
     * Translates difficulty level to text
     */
    static public function getDifficulty($dLevel) {
        switch ($dLevel) {
            case 1:
                return 'Beginner';
                break;
            case 2:
                return 'Intermediate';
                break;
            case 3:
                return 'Advanced';
                break;
            default:
                return 'Not applicable';
                break;
        }
    }

    /**
     * Get due date
     */
    static public function getDueDate($week) {
        switch ($week) {
            case 1:
                return '19/6/2013';
            break;
            case 2:
                return '26/6/2013';
            break;
            case 3:
                return '3/7/2013';
            break;
            case 4:
                return '10/7/2013';
            break;
            default:
                return 'IMMEDIATE';
            break;
        }
    }

}




?>