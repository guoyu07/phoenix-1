<?php

/**
 * Website and programme policy viewer
 *
 * @author	Yectep Studios <info@yectep.hk>
 * @version	20707
 * @package Plume
 */


define('PTP',   '../private/');
define('BETA',  false);
define('PHX_SCRIPT_TYPE',   'HTML');
define('PHX_NEWS',      true);
define('PHX_UX',        true);


// Include common ignition class
require_once(PTP . 'php/ignition.php');

// Set page switch variables
$p['title'] = 'Style Guide';
$n['styleguide'] = 'active';

// Which policy are we fetching here?
switch ($_SERVER['QUERY_STRING']) {
	case 'terms':
		$inc = 'terms';
	break;
	case 'privacy':
		$inc = 'privacy';
	break;
	case 'agepte':
		$inc = 'agepte';
	break;
	default:
		$inc = 'terms';
	break;
}

// Include header section
echo UX::grabPage('common/header_public', $p, true);
echo UX::grabPage('common/nav_public', $n, true);
echo UX::grabPage('policies/'.$inc, array('last_edit' => filemtime('../private/snippets/policies/'.$inc.'.cur.html')), true);

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

echo UX::grabPage('common/masthead', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>