<?php

/**
 * CSS style definition guide as POC
 *
 * @author	Yectep Studios <info@yectep.hk>
 * @version	20825
 * @package Plume
 */


define('PTP',   '../private/');
define('PHX_SCRIPT_TYPE',   'HTML');
define('PHX_NEWS',      true);
define('PHX_UX',        true);
define('PHX_COURSES',   true);

// Include common ignition class
require_once(PTP . 'php/ignition.php');

// Set page switch variables
$h['title'] = 'Course Listing and Search';
$n['courses'] = 'active';

// Include header section
echo UX::makeHead($h, $n);

// Page info
echo UX::makeBreadcrumb(array(	'Course Listing &amp; Search'		=> '/courses.php' ));

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

// Get data
// $stmt = Data::prepare("SELECT `CourseID`, `CourseSubj`, `CourseTitle`, `CourseSynop`, (SELECT COUNT(`classes`.`ClassID`) from `classes` WHERE `classes`.`CourseID` = `courses`.`CourseID`) as `CourseClassCount`, (SELECT MIN(`classes`.`ClassAgeMin`) from `classes` WHERE `classes`.`CourseID` = `courses`.`CourseID`) as `CourseAgeMin`, (SELECT MAX(`classes`.`ClassAgeMax`) from `classes` WHERE `classes`.`CourseID` = `courses`.`CourseID`) as `CourseAgeMax` FROM `courses` ORDER BY `CourseTitle` ASC, `CourseID` ASC");
// $stmt->execute();
// $course_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// $courses = '';
// $i = 1;

// foreach ($course_data as $course) {
// 	$courses .= '				<tr class="metalink course-row hideable-'.strtolower($course['CourseSubj']).'" data-courseid="'.$course['CourseID'].'">
// 					<td><div class="course-colorbox course-cb-'.strtolower($course['CourseSubj']).'"></div> '.strtoupper($course['CourseSubj']).str_pad($course['CourseID'], 3, '0', STR_PAD_LEFT).'</td>
// 					<td><div class="course-title"><span class="badge">Ages '.$course['CourseAgeMin'].'-'.$course['CourseAgeMax'].'</span> <a href="/view_course.php?id='.$course['CourseID'].'"><strong>'.$course['CourseTitle'].'</strong></a></div>
// 					<div id="courseid-'.$course['CourseID'].'" class="hide course-synopsis courseexpand-'.$course['CourseID'].'">'.$course['CourseSynop'].'</div></td>
// 					<td>Chester Li</td>
// 				</tr>';
// 	$i++;
// }

// Page output
if ($_SERVER['QUERY_STRING'] !== 'override') {
    echo UX::grabPage('public/courses', null, true);
} else {
    echo UX::grabPage('public/courses_beta', null, true);
}

echo UX::grabPage('common/masthead', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>