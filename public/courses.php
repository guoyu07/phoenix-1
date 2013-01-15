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

$courses = '';
$i = 1;

while ($i <= 200) {
	$courses .= '				<tr class="metalink course-row" data-courseid="'.$i.'">
					<td><div class="course-colorbox course-cb-msct"></div> MSCT'.str_pad($i, 3, '0', STR_PAD_LEFT).'</td>
					<td><div class="course-title"><span class="badge">Ages 8-12</span> <a href="/view_course.php?id='.$i.'"><strong>Web Programming for Teens</strong></a></div>
					<div id="courseid-'.$i.'" class="hide course-synopsis courseexpand-'.$i.'">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.</div></td>
					<td>Chester Li</td>
				</tr>';
	$i++;
}

// Page output
echo UX::grabPage('public/courses', null, true);

echo UX::grabPage('common/masthead', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>