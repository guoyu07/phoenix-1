<?php

/**
 * Website and programme policy viewer
 *
 * @author	Yectep Studios <info@yectep.hk>
 * @version	20707
 * @package Plume
 */


define('PTP',   '../private/');
define('PHX_SCRIPT_TYPE',   'HTML');
define('PHX_NEWS',      true);
define('PHX_UX',        true);


// Include common ignition class
require_once(PTP . 'php/ignition.php');

// Set page switch variables
$n['styleguide'] = 'active';

// Which policy are we fetching here?
switch ($_SERVER['QUERY_STRING']) {
	case 'terms':
		$inc = 'terms';
		$h['title'] = 'Terms & Conditions';
		$bc = UX::makeBreadcrumb(array('Terms and Conditions' => '/policies.php?terms'));
	break;
	case 'privacy':
		$inc = 'privacy';
		$h['title'] = 'Privacy';
		$bc = UX::makeBreadcrumb(array('Privacy Policy' => '/policies.php?privacy'));
	break;
	case 'cookies':
		$inc = 'cookies';
		$h['title'] = 'Cookies';
		$bc = UX::makeBreadcrumb(array('Cookies' => '/policies.php?cookies'));
	break;
	case 'payrefunds':
		$inc = 'payrefunds';
		$h['title'] = 'Payments & Refunds';
		$bc = UX::makeBreadcrumb(array('Payments &amp; Refunds' => '/policies.php?payrefunds'));
	break;
	case 'weather':
        $inc = 'weather';
        $h['title'] = 'Weather Warnings';
        $bc = UX::makeBreadcrumb(array('Weather Warnings' => '/policies.php?weather'));
    break;
	case 'agepte':
		$inc = 'agepte';
		$h['title'] = 'Age and PTE';
		$bc = UX::makeBreadcrumb(array('Student Age &amp; Permission to Enroll Policy' => '/policies.php?agepte'));
	break;
	default:
		header('Location: /policies.php?terms');
		exit();
	break;
}

$h['title'] .= ' | Policies';

// Include header section
echo UX::makeHead($h, $n);

// Policy wrapper
$toc = UX::grabPage('policies/toc', null, true);
echo $bc;
echo UX::grabPage('policies/'.$inc,
	array(	'last_edit'		=> date(DATETIME_FULL, filemtime('../private/snippets/policies/'.$inc.'.cur.html')),
			'policytoc'		=> $toc
	), true);

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

echo UX::grabPage('common/masthead', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>