<?php

/**
 * Course listing
 *
 * @author  Yectep Studios <info@yectep.hk>
 * @version 30122
 * @package Plume
 * @subpackage Staff
 */


define('PTP',   '../../../private/');
define('PHX_SCRIPT_TYPE',   'HTML');
define('PHX_UX',        true);
define('PHX_LAOSHI',    true);
define('PHX_COURSES',   true);


// Include common ignition class
require_once(PTP . 'php/ignition.php');

// We require a staff login for this page
if (!ACL::checkLogin('staff')) {
    header('Location: /staff/index.php?msg=error_nologin&redir='.urlencode('http://summer.cis.edu.hk/staff/ops/today.php'));
    exit();
} else {
    $_laoshi = new Laoshi($_SESSION['SSOID']);
    $_laoshi->perms(6, 7);
}

// Set default info
$h['title'] = 'Today';
$n['ops'] = 'active';
$n['my_name'] = $_laoshi->staff['StaffName'];

$week = Common::getCurrentWeek();

if (array_key_exists('checkin', $_GET)) {
    // Check in a helper
    try {
        $stmt = Data::prepare('INSERT INTO helpers_checkins (HelperID, CheckinDay, CheckinTimeIn) VALUES (:hid, DATE(NOW()), NOW())');
        $stmt->bindParam('hid', $_GET['checkin']);
        $stmt->execute();
    } catch (PDOException $e) {

    }
    header('Location: /staff/ops/today.php#!/helpers');
    exit();
}

// Page logic
$p['today'] = date('D').', Week '.$week.' ('.date(DATE_SHORT).')';
$p['period_1'] = '';

// Period 1
$stmt = Data::prepare("SELECT DISTINCT co.CourseID, co.CourseSubj, co.CourseTitle, st.StaffName, cl.RoomID, cl.TeacherID, cl.ClassID, (SELECT COUNT(e.EnrollID) FROM enrollment e, students s WHERE e.EnrollStatus = 'enrolled' AND s.StudentSubmitted = 1 AND s.StudentID = e.StudentID AND e.ClassID = cl.ClassID) as EnrollCount, cl.ClassEnrollMax FROM classes cl, courses co, staff st WHERE cl.CourseID = co.CourseID AND st.StaffID = cl.TeacherID AND cl.ClassWeek = :week AND cl.ClassPeriodBegin = 1 AND cl.ClassStatus IN ('active', 'full') ORDER BY co.CourseSubj ASC, co.CourseID ASC");
$stmt->bindParam('week', $week);
$stmt->execute();

$period1 = $stmt->fetchAll(PDO::FETCH_ASSOC);
$p['size_p1'] = sizeof($period1);

$stmt = Data::prepare("select distinct r.StudentID, CONCAT(s.StudentNameLast, ', ', s.StudentNamePreferred) as StudentName, co.CourseSubj, co.CourseID, co.CourseTitle, r.RegStatus, CONVERT_TZ(r.RegLATS, '+00:00', '+08:00') as RegLATS from registration r, students s, classes cl, courses co where r.ClassID = cl.ClassID and cl.CourseID = co.CourseID and s.StudentID = r.StudentID and r.RegDate = DATE(NOW()) and r.RegStatus IN ('absent') and cl.ClassPeriodBegin = 1 order by s.StudentNameLast ASC, s.studentNamePreferred asc");
$stmt->execute();

$abs_p1 = $stmt->fetchAll(PDO::FETCH_ASSOC);
$p['absize_p1'] = sizeof($abs_p1);


// Period 1
$stmt = Data::prepare("SELECT DISTINCT co.CourseID, co.CourseSubj, co.CourseTitle, st.StaffName, cl.RoomID, cl.TeacherID, cl.ClassID, (SELECT COUNT(e.EnrollID) FROM enrollment e, students s WHERE e.EnrollStatus = 'enrolled' AND s.StudentSubmitted = 1 AND s.StudentID = e.StudentID AND e.ClassID = cl.ClassID) as EnrollCount, cl.ClassEnrollMax FROM classes cl, courses co, staff st WHERE cl.CourseID = co.CourseID AND st.StaffID = cl.TeacherID AND cl.ClassWeek = :week AND cl.ClassPeriodBegin = 2 AND cl.ClassStatus = 'active' ORDER BY co.CourseSubj ASC, co.CourseID ASC");
$stmt->bindParam('week', $week);
$stmt->execute();

$period2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
$p['size_p2'] = sizeof($period2);

$stmt = Data::prepare("select distinct r.StudentID, CONCAT(s.StudentNameLast, ', ', s.StudentNamePreferred) as StudentName, co.CourseSubj, co.CourseID, co.CourseTitle, r.RegStatus, CONVERT_TZ(r.RegLATS, '+00:00', '+08:00') as RegLATS from registration r, students s, classes cl, courses co where r.ClassID = cl.ClassID and cl.CourseID = co.CourseID and s.StudentID = r.StudentID and r.RegDate = DATE(NOW()) and r.RegStatus IN ('absent') and cl.ClassPeriodBegin = 2 order by s.StudentNameLast ASC, s.studentNamePreferred asc");
$stmt->execute();

$abs_p2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
$p['absize_p2'] = sizeof($abs_p2);


// Period 3
$stmt = Data::prepare("SELECT DISTINCT co.CourseID, co.CourseSubj, co.CourseTitle, st.StaffName, cl.RoomID, cl.TeacherID, cl.ClassID, (SELECT COUNT(e.EnrollID) FROM enrollment e, students s WHERE e.EnrollStatus = 'enrolled' AND s.StudentSubmitted = 1 AND s.StudentID = e.StudentID AND e.ClassID = cl.ClassID) as EnrollCount, cl.ClassEnrollMax FROM classes cl, courses co, staff st WHERE cl.CourseID = co.CourseID AND st.StaffID = cl.TeacherID AND cl.ClassWeek = :week AND cl.ClassPeriodBegin = 3 AND cl.ClassStatus = 'active' ORDER BY co.CourseSubj ASC, co.CourseID ASC");
$stmt->bindParam('week', $week);
$stmt->execute();

$period3 = $stmt->fetchAll(PDO::FETCH_ASSOC);
$p['size_p3'] = sizeof($period3);

$stmt = Data::prepare("select distinct r.StudentID, CONCAT(s.StudentNameLast, ', ', s.StudentNamePreferred) as StudentName, co.CourseSubj, co.CourseID, co.CourseTitle, r.RegStatus, CONVERT_TZ(r.RegLATS, '+00:00', '+08:00') as RegLATS from registration r, students s, classes cl, courses co where r.ClassID = cl.ClassID and cl.CourseID = co.CourseID and s.StudentID = r.StudentID and r.RegDate = DATE(NOW()) and r.RegStatus IN ('absent') and cl.ClassPeriodBegin = 3 order by s.StudentNameLast ASC, s.studentNamePreferred asc");
$stmt->execute();

$abs_p3 = $stmt->fetchAll(PDO::FETCH_ASSOC);
$p['absize_p3'] = sizeof($abs_p3);



// Period 4
$stmt = Data::prepare("SELECT DISTINCT co.CourseID, co.CourseSubj, co.CourseTitle, st.StaffName, cl.RoomID, cl.TeacherID, cl.ClassID, (SELECT COUNT(e.EnrollID) FROM enrollment e, students s WHERE e.EnrollStatus = 'enrolled' AND s.StudentSubmitted = 1 AND s.StudentID = e.StudentID AND e.ClassID = cl.ClassID) as EnrollCount, cl.ClassEnrollMax FROM classes cl, courses co, staff st WHERE cl.CourseID = co.CourseID AND st.StaffID = cl.TeacherID AND cl.ClassWeek = :week AND cl.ClassPeriodBegin = 4 AND cl.ClassStatus = 'active' ORDER BY co.CourseSubj ASC, co.CourseID ASC");
$stmt->bindParam('week', $week);
$stmt->execute();

$period4 = $stmt->fetchAll(PDO::FETCH_ASSOC);
$p['size_p4'] = sizeof($period4);

$stmt = Data::prepare("select distinct r.StudentID, CONCAT(s.StudentNameLast, ', ', s.StudentNamePreferred) as StudentName, co.CourseSubj, co.CourseID, co.CourseTitle, r.RegStatus, CONVERT_TZ(r.RegLATS, '+00:00', '+08:00') as RegLATS from registration r, students s, classes cl, courses co where r.ClassID = cl.ClassID and cl.CourseID = co.CourseID and s.StudentID = r.StudentID and r.RegDate = DATE(NOW()) and r.RegStatus IN ('absent') and cl.ClassPeriodBegin = 4 order by s.StudentNameLast ASC, s.studentNamePreferred asc");
$stmt->execute();

$abs_p4 = $stmt->fetchAll(PDO::FETCH_ASSOC);
$p['absize_p4'] = sizeof($abs_p4);



// Academic
$stmt = Data::prepare("SELECT DISTINCT co.CourseID, co.CourseSubj, co.CourseTitle, st.StaffName, cl.RoomID, cl.TeacherID, cl.ClassID, cl.ClassPeriodBegin, cl.ClassPeriodEnd, (SELECT COUNT(e.EnrollID) FROM enrollment e, students s WHERE e.EnrollStatus = 'enrolled' AND s.StudentSubmitted = 1 AND s.StudentID = e.StudentID AND e.ClassID = cl.ClassID) as EnrollCount, cl.ClassEnrollMax FROM classes cl, courses co, staff st WHERE cl.CourseID = co.CourseID AND st.StaffID = cl.TeacherID AND cl.ClassWeek = :week AND co.CourseSubj NOT IN ('PHED', 'ARTS', 'LANG', 'MSCT') AND cl.ClassStatus = 'active' ORDER BY cl.ClassPeriodBegin ASC, co.CourseSubj ASC, co.CourseID ASC");
$stmt->bindParam('week', $week);
$stmt->execute();

$academic = $stmt->fetchAll(PDO::FETCH_ASSOC);
$p['size_academic'] = sizeof($academic);

$stmt = Data::prepare("select distinct r.StudentID, CONCAT(s.StudentNameLast, ', ', s.StudentNamePreferred) as StudentName, co.CourseSubj, co.CourseID, co.CourseTitle, r.RegStatus, CONVERT_TZ(r.RegLATS, '+00:00', '+08:00') as RegLATS from registration r, students s, classes cl, courses co where r.ClassID = cl.ClassID and cl.CourseID = co.CourseID and s.StudentID = r.StudentID and r.RegDate = DATE(NOW()) and r.RegStatus IN ('absent') and co.CourseSubj not in ('ARTS', 'LANG', 'MSCT', 'PHED') order by s.StudentNameLast ASC, s.studentNamePreferred asc");
$stmt->execute();

$abs_aca = $stmt->fetchAll(PDO::FETCH_ASSOC);
$p['absize_aca'] = sizeof($abs_aca);

foreach($period1 as $class) {
    $p['period_1'] .= '<tr><td><a href="/staff/teachers/registration.php?cid='.$class['ClassID'].'">'.$class['CourseTitle'].'</a> <span class="muted"> with <a href="/staff/manage/view_teacher.php?tid='.$class['TeacherID'].'" style="color:#888;">'.$class['StaffName'].'</a></span> <span class="muted small" style="float:right;"><div class="course-colorbox course-cb-'.strtolower($class['CourseSubj']).'"></div> <a href="/staff/manage/course_view.php?cid='.$class['CourseID'].'" class="tipped" style="color:#777;" title="Click to view course details">'.strtoupper($class['CourseSubj']).str_pad($class['CourseID'], 3, '0', STR_PAD_LEFT).'</a> (<span style="color:#222;">'.$class['EnrollCount'].'</span>/'.$class['ClassEnrollMax'].')</span></td><td>'.(($class['RoomID'] == 0) ? '<em class="muted">Unassigned</em>' : '<a href="/staff/ops/room_use.php?rid='.$class['RoomID'].'">'.$class['RoomID'].'</a>').'</td></tr>';
}

foreach($abs_p1 as $child) {
    $p['absences_p1'] .= '<tr class="check_child" data-student-id="'.$child['StudentID'].'"><td><a href="/staff/manage/course_view.php?cid='.$child['CourseID'].'" class="tipped" title="Details for: '.$child['CourseTitle'].'">'.$child['CourseTitle'].'</a> <span style="float:right;" class="small muted"><div class="course-colorbox course-cb-'.strtolower($child['CourseSubj']).'"></div> '.strtoupper($child['CourseSubj']).str_pad($child['CourseID'], 3, '0', STR_PAD_LEFT).'</span></td><td><a href="/staff/manage/student_schedule.php?sid='.$child['StudentID'].'">'.$child['StudentName'].'</a> <span class="small muted" style="float:right;">#'.$child['StudentID'].'</span></td><td>'.date('g:ia', strtotime($child['RegLATS'])).'</td></tr>';
}

foreach($period2 as $class) {
    $p['period_2'] .= '<tr><td><a href="/staff/teachers/registration.php?cid='.$class['ClassID'].'">'.$class['CourseTitle'].'</a> <span class="muted"> with <a href="/staff/manage/view_teacher.php?tid='.$class['TeacherID'].'" style="color:#888;">'.$class['StaffName'].'</a></span> <span class="muted small" style="float:right;"><div class="course-colorbox course-cb-'.strtolower($class['CourseSubj']).'"></div> <a href="/staff/manage/course_view.php?cid='.$class['CourseID'].'" class="tipped" style="color:#777;" title="Click to view course details">'.strtoupper($class['CourseSubj']).str_pad($class['CourseID'], 3, '0', STR_PAD_LEFT).'</a> (<span style="color:#222;">'.$class['EnrollCount'].'</span>/'.$class['ClassEnrollMax'].')</span></td><td>'.(($class['RoomID'] == 0) ? '<em class="muted">Unassigned</em>' : '<a href="/staff/ops/room_use.php?rid='.$class['RoomID'].'">'.$class['RoomID'].'</a>').'</td></tr>';
}

foreach($abs_p2 as $child) {
    $p['absences_p2'] .= '<tr class="check_child" data-student-id="'.$child['StudentID'].'"><td><a href="/staff/manage/course_view.php?cid='.$child['CourseID'].'" class="tipped" title="Details for: '.$child['CourseTitle'].'">'.$child['CourseTitle'].'</a> <span style="float:right;" class="small muted"><div class="course-colorbox course-cb-'.strtolower($child['CourseSubj']).'"></div> '.strtoupper($child['CourseSubj']).str_pad($child['CourseID'], 3, '0', STR_PAD_LEFT).'</span></td><td><a href="/staff/manage/student_schedule.php?sid='.$child['StudentID'].'">'.$child['StudentName'].'</a> <span class="small muted" style="float:right;">#'.$child['StudentID'].'</span></td><td>'.date('g:ia', strtotime($child['RegLATS'])).'</td></tr>';
}

foreach($period3 as $class) {
    $p['period_3'] .= '<tr><td><a href="/staff/teachers/registration.php?cid='.$class['ClassID'].'">'.$class['CourseTitle'].'</a> <span class="muted"> with <a href="/staff/manage/view_teacher.php?tid='.$class['TeacherID'].'" style="color:#888;">'.$class['StaffName'].'</a></span> <span class="muted small" style="float:right;"><div class="course-colorbox course-cb-'.strtolower($class['CourseSubj']).'"></div> <a href="/staff/manage/course_view.php?cid='.$class['CourseID'].'" class="tipped" style="color:#777;" title="Click to view course details">'.strtoupper($class['CourseSubj']).str_pad($class['CourseID'], 3, '0', STR_PAD_LEFT).'</a> (<span style="color:#222;">'.$class['EnrollCount'].'</span>/'.$class['ClassEnrollMax'].')</span></td><td>'.(($class['RoomID'] == 0) ? '<em class="muted">Unassigned</em>' : '<a href="/staff/ops/room_use.php?rid='.$class['RoomID'].'">'.$class['RoomID'].'</a>').'</td></tr>';
}

foreach($abs_p3 as $child) {
    $p['absences_p3'] .= '<tr class="check_child" data-student-id="'.$child['StudentID'].'"><td><a href="/staff/manage/course_view.php?cid='.$child['CourseID'].'" class="tipped" title="Details for: '.$child['CourseTitle'].'">'.$child['CourseTitle'].'</a> <span style="float:right;" class="small muted"><div class="course-colorbox course-cb-'.strtolower($child['CourseSubj']).'"></div> '.strtoupper($child['CourseSubj']).str_pad($child['CourseID'], 3, '0', STR_PAD_LEFT).'</span></td><td><a href="/staff/manage/student_schedule.php?sid='.$child['StudentID'].'">'.$child['StudentName'].'</a> <span class="small muted" style="float:right;">#'.$child['StudentID'].'</span></td><td>'.date('g:ia', strtotime($child['RegLATS'])).'</td></tr>';
}


foreach($period4 as $class) {
    $p['period_4'] .= '<tr><td><a href="/staff/teachers/registration.php?cid='.$class['ClassID'].'">'.$class['CourseTitle'].'</a> <span class="muted"> with <a href="/staff/manage/view_teacher.php?tid='.$class['TeacherID'].'" style="color:#888;">'.$class['StaffName'].'</a></span> <span class="muted small" style="float:right;"><div class="course-colorbox course-cb-'.strtolower($class['CourseSubj']).'"></div> <a href="/staff/manage/course_view.php?cid='.$class['CourseID'].'" class="tipped" style="color:#777;" title="Click to view course details">'.strtoupper($class['CourseSubj']).str_pad($class['CourseID'], 3, '0', STR_PAD_LEFT).'</a> (<span style="color:#222;">'.$class['EnrollCount'].'</span>/'.$class['ClassEnrollMax'].')</span></td><td>'.(($class['RoomID'] == 0) ? '<em class="muted">Unassigned</em>' : '<a href="/staff/ops/room_use.php?rid='.$class['RoomID'].'">'.$class['RoomID'].'</a>').'</td></tr>';
}

foreach($abs_p4 as $child) {
    $p['absences_p4'] .= '<tr class="check_child" data-student-id="'.$child['StudentID'].'"><td><a href="/staff/manage/course_view.php?cid='.$child['CourseID'].'" class="tipped" title="Details for: '.$child['CourseTitle'].'">'.$child['CourseTitle'].'</a> <span style="float:right;" class="small muted"><div class="course-colorbox course-cb-'.strtolower($child['CourseSubj']).'"></div> '.strtoupper($child['CourseSubj']).str_pad($child['CourseID'], 3, '0', STR_PAD_LEFT).'</span></td><td><a href="/staff/manage/student_schedule.php?sid='.$child['StudentID'].'">'.$child['StudentName'].'</a> <span class="small muted" style="float:right;">#'.$child['StudentID'].'</span></td><td>'.date('g:ia', strtotime($child['RegLATS'])).'</td></tr>';
}

foreach($academic as $class) {
    $p['academic'] .= '<tr><td><a href="/staff/teachers/registration.php?cid='.$class['ClassID'].'">'.$class['CourseTitle'].'</a> <span class="muted"> with <a href="/staff/manage/view_teacher.php?tid='.$class['TeacherID'].'" style="color:#888;">'.$class['StaffName'].'</a></span> <span class="muted small" style="float:right;"><div class="course-colorbox course-cb-'.strtolower($class['CourseSubj']).'"></div> <a href="/staff/manage/course_view.php?cid='.$class['CourseID'].'" class="tipped" style="color:#777;" title="Click to view course details">'.strtoupper($class['CourseSubj']).str_pad($class['CourseID'], 3, '0', STR_PAD_LEFT).'</a> (<span style="color:#222;">'.$class['EnrollCount'].'</span>/'.$class['ClassEnrollMax'].')</span></td><td>'.(($class['RoomID'] == 0) ? '<em class="muted">Unassigned</em>' : '<a href="/staff/ops/room_use.php?rid='.$class['RoomID'].'">'.$class['RoomID'].'</a>').'</td></tr>';
}

foreach($abs_aca as $child) {
    $p['absences_aca'] .= '<tr class="check_child" data-student-id="'.$child['StudentID'].'"><td><a href="/staff/manage/course_view.php?cid='.$child['CourseID'].'" class="tipped" title="Details for: '.$child['CourseTitle'].'">'.$child['CourseTitle'].'</a> <span style="float:right;" class="small muted"><div class="course-colorbox course-cb-'.strtolower($child['CourseSubj']).'"></div> '.strtoupper($child['CourseSubj']).str_pad($child['CourseID'], 3, '0', STR_PAD_LEFT).'</span></td><td><a href="/staff/manage/student_schedule.php?sid='.$child['StudentID'].'">'.$child['StudentName'].'</a> <span class="small muted" style="float:right;">#'.$child['StudentID'].'</span></td><td>'.date('g:ia', strtotime($child['RegLATS'])).'</td></tr>';
}

// Helper Checkins
$stmt = Data::query("SELECT c.CheckinID, c.CheckinDay, CONVERT_TZ(c.CheckinTimeIn, '+00:00', '+08:00') as CheckIn, CONVERT_TZ(c.CheckinTimeout, '+00:00', '+08:00') as CheckOut, a.HelperName, a.HelperEmail from helpers_checkins c, helpers_accounts a where c.HelperID = a.HelperID and c.CheckinDay = DATE(NOW()) order by HelperName asc, CheckinTimeout desc");
$checkins = $stmt->fetchAll(PDO::FETCH_ASSOC);

$p['checkins'] = '';

foreach($checkins as $c) {
    $p['checkins'] .= '<tr><td>'.$c['HelperName'].'<span class="small muted" style="float:right;">'.(($c['HelperEmail'] == '') ? '' : $c['HelperEmail']).'</span></td><td>'.date(DATETIME_FULL, strtotime($c['CheckIn'])).'</td><td>'.((date('Y', strtotime($c['CheckOut'])) == 1970) ? '<span class="muted">Still signed in (<a href="javascript:;" onclick="signOut('.$c['CheckinID'].');">Sign out</a>)</span>' : date(DATETIME_FULL, strtotime($c['CheckOut']))).'</td>';
}

// Helper List
$stmt = Data::query('SELECT * FROM `helpers_accounts` ORDER BY HelperName ASC');
$helpers = $stmt->fetchAll(PDO::FETCH_ASSOC);

$p['helpers'];

foreach($helpers as $hp) {
    $p['helpers'] .= '<option value="'.$hp['HelperID'].'">'.$hp['HelperName'].'</option>';
}

// Include header section
echo UX::makeHead($h, $n, 'common/header_staff', $_laoshi->fetchNavPage());

// Page info
echo UX::makeBreadcrumb(array(  'Staff Portal'      => '/staff/dashboard.php', 'Today' => "/staff/ops/today.php"));
echo UX::grabPage('staff/ops/today', $p, true);

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

echo UX::grabPage('common/masthead_staff', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>

