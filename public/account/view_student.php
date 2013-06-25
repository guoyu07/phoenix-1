<?php

/**
 * View student data and schedule
 *
 * @author  Yectep Studios <info@yectep.hk>
 * @version 30104
 * @package Plume
 */


define('PTP',   '../../private/');
define('PHX_SCRIPT_TYPE',   'HTML');
define('PHX_UX',        true);
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

    $_stu = new FamStu('student', $_GET['sid']);
    if ($_stu->data['FamilyID'] !== $_fam->fid) {
        header('Location: /account/dashboard.php?msg=child_exception');
        exit();
    } else {
        $_SESSION['STUID'] = $_GET['sid'];
    }
}

// Has student submitted their schedule?
if ($_stu->data['StudentSubmitted'] == '1') {
    $p['submit_note'] = '<div class="alert alert-green"><h3 style="margin-top:0;">Schedule is submitted!</h3><p>Thank you for submitting this schedule. If you need to make changes, you must contact us via email at <a href="mailto:summerprogram@cis.edu.hk">summerprogram@cis.edu.hk</a>. Fees may apply.</p><button class="button-link button-green" data-url="/account/submit.php">Review Terms and Conditions</button></div>';
    $p['button'] = '<button type="button" class="button-link" data-url="/account/courses.php/#!/show:{{suggest_program}}">Show Class Schedule</button> <button type="button" class="button-blue" onclick="window.open(\'/account/print_schedule.php?sid='.$_GET['sid'].'&v='.sha1('cis_summer:'.$_GET['sid']).'\');">Print Schedule</button>';
} else {
    $p['submit_note'] = '<div class="alert alert-red"><h3 style="margin-top:0;">No schedule submitted</h3><p>You have not yet submitted this child\'s schedule. Any "enrolled" registrations are guaranteed, however, you must submit your schedule in order for us to process payment in the future.</p><p>Submitting a schedule is a formal step that lets us know you are committed to having your child attend the Summer Program. Once you submit, you can <strong>no longer edit profile information</strong> (but can still make changes to your schedule).</p><p>Use the "Submit My Registration" button at the bottom to begin the submission process!</p><button type="button" class="button-link button-red" data-url="/account/submit.php">Submit My Registration</button></div>';
    $p['button'] = '<p>Click on any course to change or unenroll. <strong>To add a class</strong>, click "Add a Class" below and we will take you to the enrollment brochure.</p>

            <button type="button" class="button-link button-blue" data-url="/account/courses.php/#!/show:{{suggest_program}}">Add Classes to Schedule</button> ';
}

// Get student's class schedule...
$sched[1] = $_stu->getStudentSchedule(1); $week_conflict[1] = false;
$sched[2] = $_stu->getStudentSchedule(2); $week_conflict[2] = false;
$sched[3] = $_stu->getStudentSchedule(3); $week_conflict[3] = false;
$sched[4] = $_stu->getStudentSchedule(4); $week_conflict[4] = false;
$pte_count = 0;
$waitlist_count = 0;

$p['week_1'] = ''; $p['week_2'] = ''; $p['week_3'] = ''; $p['week_4'] = '';
$latest_update = strtotime($_stu->data['StudentCTS']);

foreach($sched as $i => $week) {
    if (sizeof($week) == 0) {
        $p['week_'.$i] = '<tr><td colspan="4"><em class="muted">You have not registered for any classes during Week '.$i.'.</em></td></tr>';
    } else {
        // Search conflict table
        $conflicts = array();

        foreach($week as $e) {
            if (array_key_exists($e['ClassPeriodBegin'], $conflicts)) {
                // Oops, conflict!
                $week_conflict[$i] = true;
            }

            // Prevent overlapping conflicts, set true anyway
            $conflicts[$e['ClassPeriodBegin']] = true;
            $conflicts[$e['ClassPeriodEnd']] = true;


            switch ($e['ClassPeriodBegin']) {
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

            switch ($e['ClassPeriodEnd']) {
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

            switch ($e['EnrollStatus']) {
                case 'enrolled':
                    $status  = '<img src="/assets/icons/tick.png" /> Enrolled';
                break;
                case 'waitlisted':
                    $status  = '<img src="/assets/icons/exclamation.png" /> <strong>Waitlisted</strong>';
                    $waitlist_count++;
                break;
                case 'pte_request':
                    $status  = '<img src="/assets/icons/exclamation.png" /> On request (PTE)';
                    $pte_count++;
                break;
            }

            if ($e['ClassPeriodBegin'] == $e['ClassPeriodEnd']) $length = 'single';
            else $length = 'double';

            // Check latest update...
            if (strtotime($e['EnrollLETS']) > $latest_update) $latest_update = strtotime($e['EnrollLETS']);

            $p['week_'.$i] .= '<tr><td><span class="badge badge-blue tipped" title="'.$program.' '.(($length == 'single') ? $e['ClassPeriodBegin'] : $e['ClassPeriodBegin'].'-'.$e['ClassPeriodEnd']).': '.$time_start.'-'.$time_end.'">'.$program.' '.(($length == 'single') ? $e['ClassPeriodBegin'] : $e['ClassPeriodBegin'].'-'.$e['ClassPeriodEnd']).'</span></td><td><div class="course-colorbox course-cb-'.strtolower($e['CourseSubj']).'"></div> '.(($_stu->data['StudentSubmitted'] == '1') ? '' : '<a href="/account/enroll.php?act=drop&eid='.$e['EnrollID'].'" class="tipped" title="Click cancel enrollment (will require confirmation)">').'<strong>'.$e['CourseSubj'].str_pad($e['CourseID'], 3, '0', STR_PAD_LEFT).'</strong>: '.$e['CourseTitle'].(($_stu->data['StudentSubmitted'] == '1') ? '' : '</a>').'</td><td>'.$status.'</td></tr>';
        }
    }
}

// Now go through to find week conflicts
foreach($week_conflict as $wk => $conflict) {
    switch ($wk) {
        case 1:
            $week_str = 'Week 1: June 24-28';
        break;
        case 2:
            $week_str = 'Week 2: July 2-5';
        break;
        case 3:
            $week_str = 'Week 3: July 8-12';
        break;
        case 4:
            $week_str = 'Week 4: July 15-19';
        break;
    }
    if ($conflict) {
        $p['week_'.$wk.'_status'] = '<div class="course-flag is-inactive tipped" title="'.$week_str.'"><img src="/assets/icons/cross.png" class="inactive"> Week '.$wk.': Please resolve scheduling conflict!</div>';
    } else {
        $p['week_'.$wk.'_status'] = '<div class="course-flag is-active tipped" title="'.$week_str.'"><img src="/assets/icons/tick.png" class="active"> Week '.$wk.': No scheduling conflicts found</div>';
    }
}

// Get suggested program
$p['suggest_program'] = (($_stu->data['StudentAge'] > 15) ? 'AP' : 'SP');

// Triage and get default staff page
$h['title'] = 'Profile | '.$_stu->data['StudentNamePreferred'];
$n['student'] = 'active';
$n['my_name'] = $_fam->data['FamilyName'];

// Page variables
$p['name'] = $_stu->data['StudentNamePreferred'].' '.$_stu->data['StudentNameLast'];
$p['dob'] = $_stu->data['StudentDOB'];

$todayDo = new DateTime('00:00:00');
$dobDo = new DateTime($p['dob']);
$p['age'] = $todayDo->diff($dobDo)->y;
$p['sid'] = $_stu->data['StudentID'];
$p['last_update'] = date(DATETIME_FULL, $latest_update);
$p['first_name'] = $_stu->data['StudentNameGiven'];
$p['preferred_name'] = $_stu->data['StudentNamePreferred'];
$p['last_name'] = $_stu->data['StudentNameLast'];
$p['med_meds'] = $_stu->data['StudentMedMedications'];
$p['med_cond'] = $_stu->data['StudentMedCondition'];
$p['pte_count'] = $pte_count;
$p['waitlist_count'] = $waitlist_count;
$p['pte_active'] = (($pte_count == 0) ? 'active' : 'inactive');
$p['waitlist_active'] = (($waitlist_count == 0) ? 'active' : 'inactive');
$p['pte_img'] = (($pte_count == 0) ? 'tick' : 'cross');
$p['waitlist_img'] = (($waitlist_count == 0) ? 'tick' : 'cross');

// Include header section
echo UX::makeHead($h, $n, 'common/header_public', 'common/nav_account');

// Page info
echo UX::makeBreadcrumb(array(  'My Family' => "/account/dashboard.php", $_stu->data['StudentNamePreferred'].'\'s Profile &amp; Schedule' => "/account/view_student.php?sid=".$_GET['sid']));
echo UX::grabPage('account/view_student', $p, true);

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

echo UX::grabPage('common/masthead', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>

