<?php

/**
 * Course view page
 *
 * @author  Yectep Studios <info@yectep.hk>
 * @version 30405
 * @package Plume
 */


define('PTP',   '../../private/');
define('PHX_SCRIPT_TYPE',   'HTML');
define('PHX_UX',        true);
define('PHX_LAOSHI',    true);
define('PHX_COURSES',   true);
define('PHX_STUDENT',   true);


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
        header('Location: /account/dashboard.php');
        exit();
    }

    $_stu = new FamStu('student', $_SESSION['STUID']);
    if ($_stu->data['FamilyID'] !== $_fam->fid) {
        header('Location: /account/dashboard.php');
        exit();
    }
}


// Triage and get default staff page
$h['title'] = 'Course Selection | '.$_stu->data['StudentNamePreferred'];
$n['courses'] = 'active';
$n['my_name'] = $_fam->data['FamilyName'];

// Page replacements
$p['student_name'] = $_stu->data['StudentNamePreferred'].' '.$_stu->data['StudentNameLast'];
$p['age'] = $_stu->data['StudentAge'];
$p['sid'] = $_stu->sid;

// Get course information
$course = Courses::getCourseById($_REQUEST['id']);

if ((!$course) || ($course['CourseFlagsPublic'] == '0')) {
    header("Location: /courses.php");
    exit();
}

// Decide on program
if (($course['CourseSubj'] == 'ARTS') || ($course['CourseSubj'] == 'LANG') || ($course['CourseSubj'] == 'MSCT') || ($course['CourseSubj'] == 'PHED')) {
    $course['CourseProgram'] = 'summer';
    $p['age_term'] = 'Ages';
} else {
    $course['CourseProgram'] = 'academic';
    $p['age_term'] = 'Years';
}

$course['TeacherData'] = Courses::getTeacherById($course['TeacherLead']);
$course['ClassData'] = Courses::getClassesOfCourseById($_REQUEST['id']);
$course['CourseDecription'] = Common::cleanse($course['CourseDescription']);
$course['CourseOutcomes'] = Common::cleanse($course['CourseOutcomes']);

// Build offerings table
$p['offerings'] = '';
foreach($course['ClassData'] as $class) {
    $teacher = Courses::getTeacherById($class['TeacherID']);
    switch ($class['ClassPeriodBegin']) {
        case '1':
            $time_start = '09:00';
            $program = 'Period';
        break;
        case '2':
            $time_start = '10:00';
            $program = 'Period';
        break;
        case '3':
            $time_start = '11:30';
            $program = 'Period';
        break;
        case '4':
            $time_start = '12:30';
            $program = 'Period';
        break;
        case 'A':
            $time_start = '09:30';
            $program = 'Session';
        break;
        case 'B':
            $time_start = '12:00';
            $program = 'Session';
        break;
        case 'C':
            $time_start = '14:30';
            $program = 'Session';
        break;
    }

    switch ($class['ClassPeriodEnd']) {
        case '1':
            $time_end = '09:55';
        break;
        case '2':
            $time_end = '10:55';
        break;
        case '3':
            $time_end = '12:25';
        break;
        case '4':
            $time_end = '13:25';
        break;
        case 'A':
            $time_end = '11:30';
        break;
        case 'B':
            $time_end = '14:00';
        break;
        case 'C':
            $time_end = '16:30';
        break;
    }

    switch ($class['ClassWeek']) {
        case 1:
            $week_str = 'Week 1: June 24-28';
            $weekDo = new DateTime('2013-06-24 12:00:00');
        break;
        case 2:
            $week_str = 'Week 2: July 2-5';
            $weekDo = new DateTime('2013-07-02 12:00:00');
        break;
        case 3:
            $week_str = 'Week 3: July 8-12';
            $weekDo = new DateTime('2013-07-08 12:00:00');
        break;
        case 4:
            $week_str = 'Week 4: July 15-19';
            $weekDo = new DateTime('2013-07-15 12:00:00');
        break;
    }

    $enroll_count = Courses::getActiveEnrollmentCount($class['ClassID']);

    

    if ($class['ClassWeek'] < 4) {
        $enrollstring = '<img src="/assets/icons/cross.png" /> <span class="muted">No longer available</span>';
    } else {
        $enrollstring = '<img src="/assets/icons/exclamation.png" /> Contact us to enroll';
    }
    // } elseif ($enroll_count >= $class['ClassEnrollMax']) {
    //     $enrollstring = '<img src="/assets/icons/cross.png" /> Class is full';
    // } else {
    //     if ($program == 'Period') {
    //         // Calculate age
    //         $bdayDo = new DateTime($_stu->data['StudentDOB']);
    //         $ageAtWeek = round(($weekDo->diff($bdayDo)->y + ($weekDo->diff($bdayDo)->m)/12), 1);

    //         if ((floor($ageAtWeek) > $class['ClassAgeMax']) || ($ageAtWeek < $class['ClassAgeMin'])) {
    //             if ($course["CourseEnforceAge"] == 1) {
    //                 $enrollstring = '<img src="/assets/icons/cross.png" /> Out of age range<br /><em class="muted">No PTE available</em>';
    //             } else {
    //                 if ($_stu->isStudentReserved($class['ClassWeek'], $class['ClassPeriodBegin']) || $_stu->isStudentReserved($class['ClassWeek'], $class['ClassPeriodEnd'])) {
    //                     $enrollstring = '<img src="/assets/icons/cross.png" /> <acronym class="tipped" title="You cannot be on multiple waitlist or PTEs at the same period"> Already on waitlist/PTE</acronym>';
    //                 } else {
    //                     if ($_stu->data['StudentSubmitted'] !== '1') {
    //                         $enrollstring = '<img src="/assets/icons/exclamation-shield.png" /> <a href="/account/enroll.php?act=enroll_pte&cid='.$class['ClassID'].'"><strong>Request PTE</strong></a><br /><em class="muted">Verified manually</em>';
    //                     } else {
    //                         $enrollstring = '<span class="muted">Contact us</span>';
    //                     }
    //                 }
    //             }
    //         } else {

    //             // Is student already PTE'd?
    //             if ($_stu->isStudentEnrolled($class['ClassWeek'], $class['ClassPeriodBegin']) || $_stu->isStudentEnrolled($class['ClassWeek'], $class['ClassPeriodEnd'])) {
    //                 $enrollstring = '<img src="/assets/icons/cross.png" /> Already registered at this time';
    //             } else {
    //                 if ($_stu->data['StudentSubmitted'] !== '1') {
    //                     $enrollstring = '<a href="/account/enroll.php?act=enroll&cid='.$class['ClassID'].'"><img src="/assets/icons/plus.png" /> <strong>Enroll Now</strong></a>';
    //                 } else {
    //                     $enrollstring = '<span class="muted">Contact us</span>';
    //                 }
    //             }   
    //         }

    //     } else {
    //         // Ignore for summer program
    //         // Is student already full at that time?
    //         if ($_stu->isStudentEnrolled($class['ClassWeek'], $class['ClassPeriodBegin']) || $_stu->isStudentEnrolled($class['ClassWeek'], $class['ClassPeriodEnd'])) {
    //             $enrollstring = '<img src="/assets/icons/cross.png" /> Already registered at this time';
    //         } else {
    //             $enrollstring = '<a href="/account/enroll.php?act=enroll&cid='.$class['ClassID'].'"><img src="/assets/icons/plus.png" /> <strong>Enroll Now</strong></a>';
    //         }
    //     }
    // }
    

    if (($class['ClassStatus'] == 'active') || ($class['ClassStatus'] == 'full')) {
        $p['offerings'] .= "<tr><td>".$course['CourseID'].".".$class['ClassID']."</td>
        <td>".(($teacher['TeacherName'] == '') ? '<em class="muted">To be confirmed</em>' : $teacher['TeacherName'])."</td>
        <td><span class=\"badge badge-green tipped\" title=\"".$week_str."\">Week ".$class['ClassWeek']."</span></td>
        <td><span class=\"badge badge-blue tipped\" title=\"".(($class['ClassPeriodBegin'] == $class['ClassPeriodEnd']) ? $program." ".$class['ClassPeriodBegin'] : $program."s ".$class['ClassPeriodBegin']."-".$class['ClassPeriodEnd']).": ".$time_start."-".$time_end."\">".(($class['ClassPeriodBegin'] == $class['ClassPeriodEnd']) ? $program." ".$class['ClassPeriodBegin'] : $program."s ".$class['ClassPeriodBegin']."-".$class['ClassPeriodEnd'])."</span></td>
        <td>".$class['ClassAgeMin']."-".$class['ClassAgeMax']."</td>
        <td>".$enrollstring."</td></tr>";
    }
}

$h['title'] = $course['CourseTitle'] . ' | Course View';
$n['courses'] = 'active';

// Course info
$p['course_program'] = (($course['CourseProgram'] == 'summer') ? 'Summer Program' : 'Academic Program');
$p['course_subject'] = $course['CourseSubj'];
$p['course_subj_lc'] = strtolower($course['CourseSubj']);
$p['course_formatted_id'] = str_pad($course['CourseID'], 3, '0', STR_PAD_LEFT);
$p['course_id'] = $course['CourseID'];
$p['course_loi'] = $course['CourseLOI'];
$p['course_loi_text'] = (($course['CourseLOI'] == 'en') ? 'English' : (($course['CourseLOI'] == 'zh') ? 'Putonghua (Mandarin)' : 'Bilingual (Putonghua &amp; English'));
$p['course_title'] = $course['CourseTitle'];
$p['course_beginner_active'] = (($course['CourseDifficulty'] == '1') ? 'active' : 'none');
$p['course_inter_active'] = (($course['CourseDifficulty'] == '2') ? 'active' : 'none');
$p['course_adv_active'] = (($course['CourseDifficulty'] == '3') ? 'active' : 'none');
$p['course_applicable_active'] = (($course['CourseDifficulty'] == '9') ? 'active' : 'none');
$p['course_desc'] = $course['CourseDescription'];
$p['course_synop'] = ((strlen($course['CourseSynop']) == 0) ? '(None provided)' : $course['CourseSynop']);
$p['course_prereqs'] = ((strlen($course['CoursePrereqs']) == 0) ? '(None provided)' : $course['CoursePrereqs']);
$p['course_outcomes'] = ((strlen($course['CourseOutcomes']) == 0) ? '(None provided)' : $course['CourseOutcomes']);
$p['lead_instructor_id'] = $course['TeacherData']['TeacherID'];
$p['lead_instructor_name'] = $course['TeacherData']['TeacherName'];
$p['lead_instructor_email'] = $course['TeacherData']['TeacherEmail'];
$p['computers_active'] = (($course['CourseComputers'] == 0) ? 'hide' : 'active');
$p['instruments_active'] = (($course['CourseInstruments'] == 0) ? 'hide' : 'active');
$p['activity_active'] = (($course['CourseActivity'] == 0) ? 'hide' : 'active');
$p['age_active'] = (($course['CourseEnforceAge'] == 0) ? 'hide' : 'active');
$p['enroll_active'] = (($course['CourseEnforceEnroll'] == 0) ? 'hide' : 'active');
$p['public_active'] = (($course['CourseFlagsPublic'] == 0) ? 'hide' : 'active');
$p['addon_active'] = (($course['CourseFeeAddon'] == 0) ? 'hide' : 'active');


// Include header section
echo UX::makeHead($h, $n, 'common/header_public', 'common/nav_account');

// Page info
echo UX::makeBreadcrumb(array(  'My Family' => "/account/dashboard.php", 'Course Selection' => "/account/courses.php/#!/show:SP", $course['CourseTitle'] => '/view_course.php?id='.$_REQUEST['id']));

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

// Page output
echo UX::grabPage('account/view_course', $p, true);

echo UX::grabPage('common/masthead', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);
?>

