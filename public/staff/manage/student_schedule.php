<?php

/**
 * View student data and schedule
 *
 * @author  Yectep Studios <info@yectep.hk>
 * @version 30104
 * @package Plume
 */


define('PTP',   '../../../private/');
define('PHX_SCRIPT_TYPE',   'HTML');
define('PHX_UX',        true);
define('PHX_LAOSHI',    true);
define('PHX_COURSES',   true);
define('PHX_STUDENT',   true);


// Include common ignition class
require_once(PTP . 'php/ignition.php');

// We require a staff login for this page
if (!ACL::checkLogin('staff')) {
    header('Location: /staff/index.php?msg=error_nologin');
    exit();
} else {
    $_laoshi = new Laoshi($_SESSION['SSOID']);
    $_laoshi->perms(8, 9, 10, 11, 12);
}

$_stu = new FamStu('student', $_GET['sid']);

// Has student submitted their schedule?
if ($_stu->data['StudentSubmitted'] == '1') {
    $p['submit_note'] = '<div class="alert alert-green">The parent has already submitted this schedule. The submission receipt timestamp is:<br /><strong>'.date(DATETIME_FULL, strtotime($_stu->data['StudentSubmitTS'])).'</strong></div>';
} else {
    $p['submit_note'] = '<div class="alert alert-red">Schedule hasn\'t been submitted.</div><p><button class="button" onclick="forceSubmit();" class="button-blue">Force Submit</button></p>';
}

$p['family_id'] = $_stu->data['FamilyID'];

// Get student's class schedule...
$sched[1] = $_stu->getStudentSchedule(1); $week_conflict[1] = false;
$sched[2] = $_stu->getStudentSchedule(2); $week_conflict[2] = false;
$sched[3] = $_stu->getStudentSchedule(3); $week_conflict[3] = false;
$sched[4] = $_stu->getStudentSchedule(4); $week_conflict[4] = false;

$history[1] = $_stu->getStudentSchedule(1, true); 
$history[2] = $_stu->getStudentSchedule(2, true); 
$history[3] = $_stu->getStudentSchedule(3, true);
$history[4] = $_stu->getStudentSchedule(4, true);

$p['history_json'] = var_export($history, true);

$pte_count = 0;
$waitlist_count = 0;

$p['v'] = sha1('cis_summer:'.$_GET['sid']);
$p['sid'] = $_GET['sid'];

$p['week_1'] = ''; $p['week_2'] = ''; $p['week_3'] = ''; $p['week_4'] = '';
$latest_update = strtotime($_stu->data['StudentCTS']);

foreach($sched as $i => $week) {
    if (sizeof($week) == 0) {
        $p['week_'.$i] = '<tr><td colspan="4"><em class="muted">No class registered for Week '.$i.'. Use the "Add a Class" feature to add enrollments.</em></td></tr>';
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
                    $program = 'Pd';
                break;
                case '2':
                    $time_start = '10:00';
                    $program = 'Pd';
                break;
                case '3':
                    $time_start = '11:30';
                    $program = 'Pd';
                break;
                case '4':
                    $time_start = '12:30';
                    $program = 'Pd';
                break;
                case 'A':
                    $time_start = '09:30';
                    $program = 'Sn';
                break;
                case 'B':
                    $time_start = '12:00';
                    $program = 'Sn';
                break;
                case 'C':
                    $time_start = '14:30';
                    $program = 'Sn';
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

            $p['week_'.$i] .= '<tr><td><span class="badge badge-blue tipped" title="'.$program.' '.(($length == 'single') ? $e['ClassPeriodBegin'] : $e['ClassPeriodBegin'].'-'.$e['ClassPeriodEnd']).': '.$time_start.'-'.$time_end.'"> '.$program.' '.(($length == 'single') ? $e['ClassPeriodBegin'] : $e['ClassPeriodBegin'].'-'.$e['ClassPeriodEnd']).'</span> <a href="/staff/manage/course_view.php?cid='.$e['CourseID'].'" class="tipped" title="Go to course page">'.$e['CourseTitle'].'</a> <span style="float:right;" class="small muted"><div class="course-colorbox course-cb-'.strtolower($e['CourseSubj']).'"></div> '.$e['CourseSubj'].str_pad($e['CourseID'], 3, '0', STR_PAD_LEFT).'</span></td><td>'.$status.'</td><td><button class="button-red mini tipped droppable" type="button" data-eid="'.$e['EnrollID'].'" title="Immediately drops this enrollment">Drop Class</button></td></tr>';
        }
    }
}

foreach($history as $i => $week) {
    if (sizeof($week) == 0) {
        $p['history_'.$i] = '<tr><td colspan="4"><em class="muted">No enrollment history found for Week '.$i.'.</em></td></tr>';
    } else {

        foreach($week as $e) {

            switch ($e['EnrollStatus']) {
                case 'enrolled':
                    $status  = '<img src="/assets/icons/tick.png" /> Enrolled';
                break;
                case 'waitlisted':
                    $status  = '<img src="/assets/icons/exclamation.png" /> Waitlist';
                    $waitlist_count++;
                break;
                case 'pte_request':
                    $status  = '<img src="/assets/icons/exclamation.png" /> PTE request';
                    $pte_count++;
                break;
                case 'dropped':
                    $status  = '<img src="/assets/icons/cross.png" /> Dropped';
                break;
                case 'pte_denied':
                    $status  = '<img src="/assets/icons/cross.png" /> PTE denied';
                    $pte_count++;
                break;
                default:
                    $status  = '<img src="/assets/icons/question.png" /> <em class="muted">Unknown</em>';
                    $pte_count++;
                break;
            }

            switch ($e['ClassPeriodBegin']) {
                case '1':
                    $time_start = '09:00';
                    $program = 'Pd';
                break;
                case '2':
                    $time_start = '10:00';
                    $program = 'Pd';
                break;
                case '3':
                    $time_start = '11:30';
                    $program = 'Pd';
                break;
                case '4':
                    $time_start = '12:30';
                    $program = 'Pd';
                break;
                case 'A':
                    $time_start = '09:30';
                    $program = 'Sn';
                break;
                case 'B':
                    $time_start = '12:00';
                    $program = 'Sn';
                break;
                case 'C':
                    $time_start = '14:30';
                    $program = 'Sn';
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

            if ($e['ClassPeriodBegin'] == $e['ClassPeriodEnd']) $length = 'single';
            else $length = 'double';

            // Check latest update...
            if (strtotime($e['EnrollLETS']) > $latest_update) $latest_update = strtotime($e['EnrollLETS']);

            $p['history_'.$i] .= '<tr><td><span class="badge badge-blue tipped" title="'.$program.' '.(($length == 'single') ? $e['ClassPeriodBegin'] : $e['ClassPeriodBegin'].'-'.$e['ClassPeriodEnd']).': '.$time_start.'-'.$time_end.'"> '.$program.' '.(($length == 'single') ? $e['ClassPeriodBegin'] : $e['ClassPeriodBegin'].'-'.$e['ClassPeriodEnd']).'</span> <a href="/staff/manage/course_view.php?cid='.$e['CourseID'].'" class="tipped" title="Go to course page">'.$e['CourseTitle'].'</a> <span style="float:right;" class="small muted"><div class="course-colorbox course-cb-'.strtolower($e['CourseSubj']).'"></div> '.$e['CourseSubj'].str_pad($e['CourseID'], 3, '0', STR_PAD_LEFT).'</span></td><td class="metalink tipped" title="Last update: '.date(DATETIME_SHORT, strtotime($e['EnrollLETS'])).'">'.$status.'</td></tr>';
        }
    }
}

// Registration history
$i = 1;
$registration = array();
$reg_his = array();

while ($i <= 4) {
    $stmt = Data::prepare('SELECT co.CourseSubj, co.CourseID, co.CourseTitle, cl.ClassID, cl.ClassPeriodBegin, cl.ClassPeriodEnd, r.RegStatus, r.RegDate FROM `registration` r INNER JOIN `classes` cl ON cl.ClassID = r.ClassID INNER JOIN `courses` co ON co.CourseID = cl.CourseID WHERE r.StudentID = :sid AND cl.ClassWeek = :week GROUP BY cl.ClassID, r.RegDate ORDER BY r.RegDate , cl.ClassPeriodBegin ASC, r.RegLATS DESC');
    $stmt->bindParam('sid', $_stu->data['StudentID'], PDO::PARAM_INT);
    $stmt->bindParam('week', $i, PDO::PARAM_INT);
    $stmt->execute();
    $registration[$i] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $i++;
}

$p['test'] = var_export($registration, true);

foreach($registration as $week => $week_data) {
    foreach($week_data as $reg) {
        $prefix = (($reg['RegStatus'] == 'present') ? '<img src="/assets/icons/tick.png" />' : '<img src="/assets/icons/cross.png" />');

        $reg_his[$week] .= '<tr><td style="padding-left:0.5em;padding-right:0.35em;" class="tipped" title="'.date('l, '.DATE_FULL, strtotime($reg['RegDate'])).'">'.date('D', strtotime($reg['RegDate'])).' <span class="badge" style="float:right;">P'.(($reg['ClassPeriodBegin'] == $reg['ClassPeriodEnd']) ? $reg['ClassPeriodBegin'] : $reg['ClassPeriodBegin'].'-'.$reg['ClassPeriodEnd']).'</span></td><td><a href="/manage/class_edit.php?cid='.$reg['ClassID'].'" class="tipped" title="View class roster">'.$reg['CourseTitle'].'</a> <span class="small muted" style="float:right;">'.strtoupper($reg['CourseSubj']).str_pad($reg['CourseID'], 3, '0', STR_PAD_LEFT).' <div class="course-colorbox course-cb-'.strtolower($reg['CourseSubj']).'"></div></span></td><td>'.$prefix.' '.ucfirst($reg['RegStatus']).'</td></tr>';
    }
}

$p['reghis_1'] = $reg_his[1];
$p['reghis_2'] = $reg_his[2];
$p['reghis_3'] = $reg_his[3];
$p['reghis_4'] = $reg_his[4];

// Get suggested program
$p['suggest_program'] = (($_stu->data['StudentAge'] > 15) ? 'AP' : 'SP');

// Triage and get default staff page
$h['title'] = 'Profile | '.$_stu->data['StudentNamePreferred'];

$n['management'] = 'active';
$n['my_name'] = $_laoshi->staff['StaffName'];

// Include header section
echo UX::makeHead($h, $n, 'common/header_staff', $_laoshi->fetchNavPage());

// Page variables
$p['name'] = $_stu->data['StudentNamePreferred'].' '.$_stu->data['StudentNameLast'];
$p['dob'] = date(DATE_FULL, strtotime($_stu->data['StudentDOB']));

$todayDo = new DateTime('00:00:00');
$dobDo = new DateTime($p['dob']);
$p['age'] = $todayDo->diff($dobDo)->y;
$p['sid'] = $_stu->data['StudentID'];
$p['last_update'] = date(DATETIME_FULL, $latest_update);
$p['first_name'] = $_stu->data['StudentNameGiven'];
$p['preferred_name'] = $_stu->data['StudentNamePreferred'];
$p['last_name'] = $_stu->data['StudentNameLast'];
$p['med_meds'] = (($_stu->data['StudentMedMedications'] == '') ? '<em class="muted">(None)</em>' : $_stu->data['StudentMedMedications']);
$p['med_cond'] = (($_stu->data['StudentMedCondition'] == '') ? '<em class="muted">(None)</em>' : $_stu->data['StudentMedCondition']);
$p['pte_count'] = $pte_count;
$p['emer_name'] = $_stu->data['StudentECName'];
$p['emer_phone'] = $_stu->data['StudentECPhone'];
$p['emer_relation'] = $_stu->data['StudentECRelationship'];
$p['school'] = $_stu->data['StudentSchool'];
$p['gender'] = (($_stu->data['StudentGender'] == 'F') ? 'Female' : 'Male');
$p['waitlist_count'] = $waitlist_count;
$p['pte_active'] = (($pte_count == 0) ? 'active' : 'inactive');
$p['waitlist_active'] = (($waitlist_count == 0) ? 'active' : 'inactive');
$p['pte_img'] = (($pte_count == 0) ? 'tick' : 'cross');
$p['waitlist_img'] = (($waitlist_count == 0) ? 'tick' : 'cross');

// Page info
echo UX::makeBreadcrumb(array(  'Staff Portal'      => '/staff/dashboard.php', 'Student List' => "/staff/manage/students.php", $_stu->data['StudentNamePreferred'].'\'s Profile &amp; Schedule' => "/staff/manage/student_schedule.php?sid=".$_GET['sid']));
echo UX::grabPage('staff/manage/student_schedule', $p, true);

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

echo UX::grabPage('common/masthead_staff', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>

