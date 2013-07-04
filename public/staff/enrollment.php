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
define('PHX_SCRIPT_TYPE',   'CSV');


// Include common ignition class
require_once(PTP . 'php/ignition.php');

$fileName = 'Week'.$_GET['week'].'Enrollment-'.time().'.csv';
 
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Content-Description: File Transfer');
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename={$fileName}");
header("Expires: 0");
header("Pragma: public");
 
$fh = @fopen( 'php://output', 'w' );

$stmt = Data::prepare("SELECT cl.ClassID as `Class ID`, co.CourseSubj as `Subject`, co.CourseID as `Course ID`, co.CourseTitle as `CourseTitle`, st.StaffName as `Teacher`, CONCAT(cl.ClassAgeMin, '-', cl.ClassAgeMax, ' yrs.') as `Age Range`, CONCAT('Period ', cl.ClassPeriodBegin, '-', cl.ClassPeriodEnd) as `Period`, CONCAT((SELECT COUNT(e.EnrollID) FROM enrollment e WHERE e.ClassID = cl.ClassID AND e.EnrollStatus = 'enrolled'), '/', cl.ClassEnrollMax, ' enrolled') as `EA` FROM classes cl, courses co, staff st WHERE st.StaffID = co.TeacherLead AND cl.CourseID = co.CourseID AND cl.ClassWeek = :week AND cl.ClassStatus = 'active' AND co.CourseSubj IN ('PHED', 'ARTS', 'MSCT', 'LANG') ORDER BY cl.ClassPeriodBegin ASC, co.CourseSubj ASC");
$stmt->bindParam('week', $_GET['week']);
$stmt->execute();

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
$headerDisplayed = false;
 
foreach ( $results as $data ) {
    fputcsv($fh, $data);
}
// Close the file
fclose($fh);
// Make sure nothing else is sent, our file is done
exit;

?>

