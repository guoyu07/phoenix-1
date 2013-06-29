<?php

/**
 * The StuFam class provides for two objects - one for the current student or family
 * and includes data lookup functions
 */
class FamStu {

    /** 
     * A new StuFam object defines either the current student or family object
     * @param   str $type   Parent or student
     * @param   int $id     Family or student ID
     */
    public function __construct($type, $id) {
        switch ($type) {
            case 'family':
                try {
                    $stmt = Data::prepare('SELECT * FROM `families` WHERE `ObjID` = :famid LIMIT 1');
                    $stmt->bindParam('famid', $id, PDO::PARAM_INT);
                    $stmt->execute();
                    $retval = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (sizeof($retval) < 1) {
                        die('ERROR');
                        $this->type = 'invalid';
                    } else {
                        $this->type = 'parent';
                        $this->data = $retval;
                        $this->fid  = $retval['FamilyID'];
                    }
                } catch (PDOException $e) {
                    $this->type = 'invalid';
                }
            break;
            case 'student':
                try {
                    $stmt = Data::prepare('SELECT * FROM `students` WHERE `StudentID` = :stuid LIMIT 1');
                    $stmt->bindParam('stuid', $id, PDO::PARAM_INT);
                    $stmt->execute();
                    $retval = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (sizeof($retval) < 1) {
                        $this->type = 'invalid';
                    } else {
                        $this->type = 'student';
                        $this->data = $retval;
                        $this->sid  = $retval['StudentID'];

                        $todayDo = new DateTime('2013-06-24 00:00:00');
                        $bdayDo = new DateTime($retval['StudentDOB']);

                        $this->data['StudentAge'] = $todayDo->diff($bdayDo)->y;

                    }
                } catch (PDOException $e) {
                    $this->type = 'invalid';
                }
            break;
            default:
                $this->type = 'uninitiated';
            break;
        }
        return;
    }

    /**
     * Gets email address
     */
    public function getFamEmail() {
        try {
            $stmt = Data::prepare('SELECT o.`ObjEmail` FROM `sso_objects` o WHERE `ObjID` = :objid LIMIT 0,1');
            $stmt->bindParam('objid', $this->data['ObjID']);
            $stmt->execute();
            $rv = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            Common::logAction('FamStu::getFamEmail', 'failed', 'FamID='.$this->fid, $e->getMessage());
            return;
        }
        return $rv['ObjEmail'];
    }

    /**
     * Returns number of children in a family's account and their 4-Status
     */
    public function getChildren() {
        try {
            $stmt = Data::prepare('SELECT * FROM `students` WHERE `FamilyID` = :famid');
            $stmt->bindParam('famid', $this->fid, PDO::PARAM_INT);
            $stmt->execute();
            $this->children = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOExcaption $e) {
            Common::logAction('FamStu::getChildren', 'failed', 'FamID='.$this->fid, $e->getMessage());
            return;
        }
        return;
    }

    /**
     * Sees if account registered email address is from CIS
     */
    public function getCosStatus() {
        try {
            $stmt = Data::prepare('SELECT ObjEmail FROM `sso_objects` WHERE `ObjID` = :oid AND `ObjType` = 7 LIMIT 1');
            $stmt->bindParam('oid', $this->data['ObjID']);
            $stmt->execute();
            $rv = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!array_key_exists('ObjEmail', $rv)) {
                // Invalid SSO Object
                return false;
            } else {
                preg_match('/@(.*)/', $rv['ObjEmail'], $domain);
                if ($domain[0] == '@cis.edu.hk') return true;
                else return false;
            }
        } catch (PDOException $e) {
            Common::logAction('FamStu::getCosStatus', 'failed', 'FamID='.$this->fid, $e->getMessage());
            return;
        }

    }

    /**
     * Gets a student's schedule for a particular week, if given
     * @param   string  $week   Week to look up. Option: if left blank, shows all 4 weeks
     */
    public function getStudentSchedule($week = 'all', $getdropped = false) {
        if ($week == 'all') $weekIn = '1,2,3,4';
        else $weekIn = (int) $week;

        try {
            $stmt = Data::prepare('SELECT e.*, c.*, cs.CourseTitle, cs.CourseSubj, cs.CourseID FROM `enrollment` e, `classes` c, `courses` cs WHERE e.StudentID = :stuid AND e.ClassID = c.ClassID AND c.CourseID = cs.CourseID AND c.ClassWeek IN ('.$weekIn.') AND e.EnrollStatus IN ("waitlisted", "pte_request", "enrolled"'.(($getdropped === true) ? ', "dropped", "pte_denied"' : '').') ORDER BY c.ClassWeek ASC, c.ClassPeriodBegin ASC');
            $stmt->bindParam('stuid', $this->sid, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            Common::logAction('FamStu::getStudentSchedule', 'failed', 'StuID='.$this->sid, $e->getMessage());
            return;
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    /**
     * Is the student enrolled (status: "enrolled") during a particular week/period?
     * @param   string  $week   Week to look up.
     * @param   string  $period Period to look up
     */
    public function isStudentEnrolled($week, $period) {
        try {
            $stmt = Data::prepare('SELECT e.* FROM `enrollment` e, `classes` c WHERE c.ClassWeek = :week AND (c.ClassPeriodBegin = :period OR c.ClassPeriodEnd = :period) AND c.ClassID = e.ClassID AND e.StudentID = :stuid  AND e.EnrollStatus = "enrolled"');
            $stmt->bindParam('stuid', $this->sid, PDO::PARAM_INT);
            $stmt->bindParam('period', $period, PDO::PARAM_INT);
            $stmt->bindParam('week', $week, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            Common::logAction('FamStu::isStudentEnrolled', 'failed', 'StuID='.$this->sid, $e->getMessage());
            return;
        }

        if (sizeof($stmt->fetchAll(PDO::FETCH_ASSOC)) > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Is the student reserved (status: anything but dropped) during a particular week/period?
     * @param   string  $week   Week to look up.
     * @param   string  $period Period to look up
     */
    public function isStudentReserved($week, $period) {
        try {
            $stmt = Data::prepare('SELECT e.* FROM `enrollment` e, `classes` c WHERE c.ClassWeek = :week AND (c.ClassPeriodBegin = :period OR c.ClassPeriodEnd = :period) AND c.ClassID = e.ClassID AND e.StudentID = :stuid  AND e.EnrollStatus IN ("waitlisted","pte_request")');
            $stmt->bindParam('stuid', $this->sid, PDO::PARAM_INT);
            $stmt->bindParam('period', $period, PDO::PARAM_INT);
            $stmt->bindParam('week', $week, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            Common::logAction('FamStu::isStudentReserved', 'failed', 'StuID='.$this->sid, $e->getMessage());
            return;
        }

        if (sizeof($stmt->fetchAll(PDO::FETCH_ASSOC)) > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns a list of family data
     */
    static public function getFamilyList() {
        try {
            $stmt = Data::prepare('SELECT f.*, (SELECT COUNT(DISTINCT s.`StudentID`) FROM `students` s WHERE s.`FamilyID` = f.`FamilyID`) as `ChildCount` FROM `families` f ORDER BY f.`FamilyName` ASC');
            $stmt->execute();
        } catch (PDOException $e) {
            Common::logAction('FamStu::getFamilyList', 'failed', 'UNK=Unknown', $e->getMessage());
            return null;
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Returns a family's data, including child data
     * @param   int $famId
     */
    static public function getFamilyById($famId) {
        try {
            $stmt = Data::prepare('SELECT f.*, s.`ObjEmail` as `FamilyEmail` FROM `sso_objects` s, `families` f WHERE f.`FamilyID` = :famid AND f.`ObjID` = s.`ObjID` LIMIT 1');
            $stmt->bindParam('famid', $famId, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            Common::logAction('FamStu::getFamilyById', 'failed', 'UNK=Unknown', $e->getMessage());
            return null;
        }

        $fam = $stmt->fetch(PDO::FETCH_ASSOC);

        // Kill it before it lays eggs
        if (sizeof($fam) == 0) {
            return false;
        }

        try {
            $stmt = Data::prepare('SELECT * FROM `students` WHERE `FamilyID` = :famid ORDER BY `StudentNamePreferred` ASC');
            $stmt->bindParam('famid', $famId);
            $stmt->execute();
        } catch (PDOException $e) {
            Common::logAction('FamStu::getFamilyById::Q2', 'failed', 'UNK=Unknown', $e->getMessage());
        }

        $stu = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array('family' => $fam, 'children' => $stu);
    }

    /**
     * Returns PTE list, given an optional $famId
     * @param   int $famId
     */
    static public function getPTEList($famId = false) {
        if ($famId) {
            try {
                $stmt = Data::prepare("SELECT s.`StudentID`, s.`FamilyID`, CONCAT(s.`StudentNamePreferred`, ' ', s.`StudentNameLast`) as `StudentName`, s.`StudentDOB`, c.`CourseID`, c.`CourseTitle`, cl.`ClassAgeMin`, cl.`ClassAgeMax`, cl.`ClassWeek`, cl.`ClassPeriodBegin`, cl.`ClassPeriodEnd`, e.`EnrollID`, e.`EnrollStatus`, (SELECT COUNT(DISTINCT `EnrollID`) FROM `enrollment` WHERE `ClassID` = cl.`ClassID` AND `EnrollStatus` ='enrolled') as `EnrollCount`, cl.`ClassEnrollMax`, e.`EnrollCTS` FROM `enrollment` e, `students` s, `courses` c, `classes` cl WHERE e.`ClassID` = cl.`ClassID` AND c.`CourseID` = cl.`CourseID` AND e.`StudentID` = s.`StudentID` AND e.`EnrollStatus` LIKE '%pte%' AND s.`FamilyID` = :famid ORDER BY s.`StudentNameLast` ASC, s.`StudentNamePreferred` ASC");
                $stmt->bindParam('famid', $famId, PDO::PARAM_INT);
                $stmt->execute();
            } catch (PDOException $e) {
                Common::logAction('FamStu::getPTEList', 'failed', 'UNK=Unknown', $e->getMessage());
                return null;
            }
        } else {
            // Return everyone's PTE
            try {
                $stmt = Data::query("SELECT s.`StudentID`, s.`FamilyID`, CONCAT(s.`StudentNamePreferred`, ' ', s.`StudentNameLast`) as `StudentName`, s.`StudentDOB`, c.`CourseID`, c.`CourseTitle`, cl.`ClassAgeMin`, cl.`ClassAgeMax`, cl.`ClassWeek`, cl.`ClassPeriodBegin`, cl.`ClassPeriodEnd`, e.`EnrollID`, e.`EnrollStatus`, (SELECT COUNT(DISTINCT `EnrollID`) FROM `enrollment` WHERE `ClassID` = cl.`ClassID` AND `EnrollStatus` ='enrolled') as `EnrollCount`, cl.`ClassEnrollMax`, e.`EnrollCTS` FROM `enrollment` e, `students` s, `courses` c, `classes` cl WHERE e.`ClassID` = cl.`ClassID` AND c.`CourseID` = cl.`CourseID` AND e.`StudentID` = s.`StudentID` AND e.`EnrollStatus` LIKE '%pte%' ORDER BY s.`StudentNameLast` ASC, s.`StudentNamePreferred` ASC");
            } catch (PDOException $e) {
                Common::logAction('FamStu::getPTEList', 'failed', 'UNK=Unknown', $e->getMessage());
                return null;
            }
        }

        // Return the list of PTE's
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    }


    /**
     * Gets the day difference of the student given the week
     */
    static public function getAgeDifference($week, $dob, $minage) {
        $dobDo = new DateTime($dob);

        $minage = (($minage == '3.5') ? 'P3Y6M' : 'P'.$minage.'Y');
        $dobDo->add(new DateInterval($minage));

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

        return $weekDo->diff($dobDo, true);
    }

    /**
     * Is the student enrolled (status: "enrolled") during a particular week/period?
     * @param   string  $week   Week to look up.
     * @param   string  $period Period to look up
     */
    static public function isStudentEnrolledStatic($week, $period, $stuid) {
        try {
            $stmt = Data::prepare('SELECT e.* FROM `enrollment` e, `classes` c WHERE c.ClassWeek = :week AND (c.ClassPeriodBegin = :period OR c.ClassPeriodEnd = :period) AND c.ClassID = e.ClassID AND e.StudentID = :stuid  AND e.EnrollStatus = "enrolled"');
            $stmt->bindParam('stuid', $this->sid, PDO::PARAM_INT);
            $stmt->bindParam('period', $period, PDO::PARAM_INT);
            $stmt->bindParam('week', $week, PDO::PARAM_INT);
            $stmt->bindParam('stuid', $stuid, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            Common::logAction('FamStu::isStudentEnrolled', 'failed', 'StuID='.$this->sid, $e->getMessage());
            return;
        }

        if (sizeof($stmt->fetchAll(PDO::FETCH_ASSOC)) > 0) {
            return true;
        } else {
            return false;
        }
    }

}