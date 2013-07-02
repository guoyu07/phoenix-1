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

// // We require a staff login for this page
// if (!ACL::checkLogin('public')) {
//     header('Location: ./login.php?msg=error_nologin');
//     exit();
// } else {
//     $_fam = new FamStu('family', $_SESSION['SSOID']);
//     $_fam->getChildren();
//     if (sizeof($_fam->children) == 0) {
//         header('Location: /account/add_child.php?msg=new_acct');
//         exit();
//     }

//     $_stu = new FamStu('student', $_GET['sid']);
//     if ($_stu->data['FamilyID'] !== $_fam->fid) {
//         header('Location: /account/dashboard.php?msg=child_exception');
//         exit();
//     } else {
//         $_SESSION['STUID'] = $_GET['sid'];
//     }

//     if (sha1('cis_summer:'.$_GET['sid']) !== $_GET['v']) {
//         header('Location: /account/dashboard.php?msg=child_exception');
//         exit();
//     }
// }

if (sha1('cis_summer:'.$_GET['sid']) !== $_GET['v']) {
    header('Location: /account/dashboard.php?msg=child_exception');
    exit();
} else {
    $_stu = new FamStu('student', $_GET['sid']);
}


// Get student's class schedule...
$sched[1] = $_stu->getStudentSchedule(1);
$sched[2] = $_stu->getStudentSchedule(2);
$sched[3] = $_stu->getStudentSchedule(3);
$sched[4] = $_stu->getStudentSchedule(4);


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

            $p['week_'.$i] .= '<tr><td><span class="badge badge-blue tipped" title="'.$program.' '.(($length == 'single') ? $e['ClassPeriodBegin'] : $e['ClassPeriodBegin'].'-'.$e['ClassPeriodEnd']).': '.$time_start.'-'.$time_end.'">'.$program.' '.(($length == 'single') ? $e['ClassPeriodBegin'] : $e['ClassPeriodBegin'].'-'.$e['ClassPeriodEnd']).'</span> '.$time_start.'-'.$time_end.'</td><td>'.(($_stu->data['StudentSubmitted'] == '1') ? '' : '<a href="/account/enroll.php?act=drop&eid='.$e['EnrollID'].'" class="tipped" title="Click cancel enrollment (will require confirmation)">').'<strong>'.$e['CourseSubj'].str_pad($e['CourseID'], 3, '0', STR_PAD_LEFT).'</strong>: '.$e['CourseTitle'].(($_stu->data['StudentSubmitted'] == '1') ? '' : '</a>').'</td><td>'.$e['RoomID'].'</td></tr>';
        }
    }
}

// Get suggested program
$p['suggest_program'] = (($_stu->data['StudentAge'] > 15) ? 'AP' : 'SP');

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
$p['v'] = $_GET['v'];


echo UX::grabPage('account/print_schedule', $p, true);

?>

