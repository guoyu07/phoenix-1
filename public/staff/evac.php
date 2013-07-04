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

$fileName = 'EvacList_'.time().'.csv';
 
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Content-Description: File Transfer');
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename={$fileName}");
header("Expires: 0");
header("Pragma: public");
 
$fh = @fopen( 'php://output', 'w' );

$stmt = Data::prepare("SELECT st.StaffName as Teacher, CONCAT(co.CourseSubj, co.CourseID) AS Course, cl.ClassPeriodBegin, UPPER(CONCAT(s.StudentNameLast, ', ', s.StudentNameGiven)) as Student, CONCAT(f.FamilyName, ' (', f.FamilyPhoneMobile, ')') as Parent FROM students s, families f, classes cl, courses co, enrollment e, staff st WHERE st.StaffID = cl.TeacherID AND s.FamilyID = f.FamilyID AND s.StudentSubmitted = 1 AND s.StudentID = e.StudentID AND e.EnrollStatus = 'enrolled' AND e.ClassID = cl.ClassID AND cl.CourseID = co.CourseID AND cl.ClassStatus in ('full', 'active') AND cl.ClassWeek = :week ORDER BY st.StaffName ASC, cl.ClassPeriodBegin ASC, s.StudentNameLast ASC");
$stmt->bindParam('week', Common::getCurrentWeek());
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

