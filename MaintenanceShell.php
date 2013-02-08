<?php
/***********************************************************
 * Name:     Maintenance Shell
 * Desc:     Adds a special page to provide access to maintenance scripts
 *
 * Version:  0.3.2
 *
 * Author:   Andrew Fitzgerald (contact@swiftlytilting.com)
 * Homepage: https://www.mediawiki.org/wiki/Extension:MaintenanceShell
 *           http://www.swiftlytilting.com/
 *
 * License:  GNU GPL
 *
 ***********************************************************
 */


# Alert the user that this is not a valid entry point to MediaWiki if they try to access the special pages file directly.
if (!defined('MEDIAWIKI')) {
		echo wfMsg_MS('maintshell-installfail3');
		exit( 1 );
}

$wgExtensionCredits['specialpage'][] = array(
	'name' => 'MaintenanceShell',
	'author' => '[http://swiftlytilting.com Andrew Fitzgerald]',
	'url' => 'https://www.mediawiki.org/wiki/Extension:MaintenanceShell',
	'description' => 'Adds a special page to provide access to maintenance scripts.',
	'descriptionmsg' => 'maintenanceshell-desc',
	'version' => '0.3.2',
);

$dir = dirname(__FILE__) . '/';

$wgAutoloadClasses['MaintenanceShell'] = $dir . 'MaintenanceShell_body.php'; # Tell MediaWiki to load the extension body.
$wgExtensionMessagesFiles['MaintenanceShell'] = $dir . 'MaintenanceShell.i18n.php';
$wgExtensionAliasesFiles['MaintenanceShell'] = $dir . 'MaintenanceShell.alias.php';
$wgSpecialPages['MaintenanceShell'] = 'MaintenanceShell'; # Let MediaWiki know about your new special page.

// Special page group for MW 1.13+
$wgSpecialPageGroups['MaintenanceShell'] = 'wiki';

// New user right - required to access Special:MaintenanceShell
$wgAvailableRights[] = 'maintenanceshell';

// check that there is a group assigned to maintenanceshell
$wgMaintShellPermissions = 0;
foreach ($wgGroupPermissions as $v)
{ $wgMaintShellPermissions += array_key_exists('maintenanceshell', $v) ? 1 : 0;
}

// load language file
require_once($IP . '/extensions/MaintenanceShell/MaintenanceShell.i18n.php');

// check for custom settings

$wgMaintenanceShellLang = isset($wgLanguageCode) ? $wgLanguageCode : 'en';
$maintenance_path = isset( $wgMaintenancePath) ?  $wgMaintenancePath :  $IP . '/maintenance/';


// catch operations before wiki does anything, so we can act like we're coming from the command line
// we use $_POST because $wgRequest isn't initialized, not really needed anyways

if (!array_key_exists('commandline', $_POST)
	&& !array_key_exists('token', $_POST) // make sure it was really from the user and not a fake
) {
	return;	 // bail if we're not coming from the command line form
}
else {
	// define some functions we'll need.  could load then from MW but it's faster to write them
	// than figure out how to cleanly load them from MW
	// at least now they don't load unless we're actually using the maintenance shell

	function wfMsg_MS($key) {
		global $wgMaintenanceShellLang, $messages, $wgRequest;
		$args = func_get_args();
		array_shift( $args );


		if (array_key_exists($wgMaintenanceShellLang, $messages)
			&& array_key_exists($key, $messages[$wgMaintenanceShellLang])
		) {
			$ret = $messages[$wgMaintenanceShellLang][$key];
		}
		elseif (array_key_exists('en', $messages)) {
			$ret = $messages['en'][$key];
		} else {
			$ret = '[ERROR: string not found for this language]';
		}

		return wfMsgReplaceArgs_MS($ret, $args);
	}

	function wfMsgReplaceArgs_MS( $message, $args ) {
		# Fix windows line-endings
		# Some messages are split with explode("\n", $msg)
		$message = str_replace( "\r", '', $message );

		// Replace arguments
		if ( count( $args ) ) {
				if ( is_array( $args[0] ) ) {
						$args = array_values( $args[0] );
				}
				$replacementKeys = array();
				foreach( $args as $n => $param ) {
						$replacementKeys['$' . ($n + 1)] = $param;
				}
				$message = strtr( $message, $replacementKeys );
		}

		return $message;
	}
}


$maintshell_pagename = wfMsg_MS( 'maintshell-pagename');

if (array_key_exists('title', $_REQUEST)
	&& ($_REQUEST['title'] == $maintshell_pagename)
) {
	// first lets check to see if we're installed correctly
	if ($wgMaintShellPermissions === 0) {
		echo wfMsg_MS('maintshell-installfail');
		exit;
	}

	// set up system to verify user permissions
	require_once('./includes/Setup.php');

	if ( $wgUser->isBlocked()
		|| wfReadOnly()
		|| !$wgUser->isAllowed( 'maintenanceshell' )
		|| $_POST['token'] !== $wgUser->getToken()
	) {

		$head_redirect = 'Location: '
			. ($_SERVER['SERVER_PORT'] == "443" ? "https" : "http")
			. "://"
			. $_SERVER['SERVER_NAME']
			. ($_SERVER['SERVER_PORT'] == "80" ? "" : $_SERVER['SERVER_PORT'])
			. $_SERVER['SCRIPT_NAME']
			. '?title='
			. $maintshell_pagename;

		header($head_redirect);
		return;
	}

	echo '<a href="' . $_SERVER['SCRIPT_NAME'] . '?title=' . $maintshell_pagename . '">'. wfMsg_MS('maintshell-return') .  '</a>';

	echo '<hr />';
	$script = trim($_POST['script']);

	if ($script) {
		$script = str_replace(array('.', '/'), '', $script);
		$scriptHTML  = MaintenanceShell::HTMLescape($script);

		$commandline = (array_key_exists('commandline', $_POST) ? trim($_POST['commandline']) : '');
		$commandlineHTML = MaintenanceShell::HTMLescape($commandline);


		if (file_exists(trim($maintenance_path . $script . '.php'))) {
			// display shell frame
			echo '<div style="border: 5px solid gray; background-color: black; color: green; padding: 1em;">';
			echo '<pre style="white-space: -moz-pre-wrap; white-space: -pre-wrap; white-space: -o-pre-wrap; white-space: pre-wrap; word-wrap: break-word;"><b>';
			chdir(isset($wgMaintenanceShellDir) ? $wgMaintenanceShellDir : $maintenance_path);
			echo getcwd() . '$ php '. (isset($wgMaintenanceShellDir) ? $maintenance_path : '') .$script . '.php ' . $commandlineHTML  . "</b>\n\n";

			// make commandline.inc think we're coming from the command line
			// Unset request method and build $argv
			unset($_SERVER['REQUEST_METHOD']);

			$commandline = str_ireplace('{{root}}', $_SERVER['DOCUMENT_ROOT'], $commandline);

			// handle quote marks in command line

			preg_match_all('%\"(.*)\"%Us', $commandline, $matches);

			foreach ($matches[1] as $n => $v) {
				$commandline = str_replace($v, str_replace(' ', "\n", $v), $temp_command );
			}

			$temp_command = preg_replace('% +%', ' ', $commandline);
			$argv = explode(' ',basename($_SERVER["PHP_SELF"]) . ' ' . $commandline);

			$search = array( '"', "\n");
			$replace = array('', ' ');
			foreach($argv as $n => $v) {
				$argv[$n] = str_replace($search, $replace, $v);
			}

			$argc = count($argv);

			// catch exit calls from within the called script
			function exit_callback($param = false) {
				echo '</pre></div>';
				exit;
			}

			register_shutdown_function('exit_callback');

			// needed for MW 1.15
			// apparently sometimes 2 ob_starts are needed?!  not sure why..
			// it's best when the output isn't buffered at all, then in firefox it displays as the form
			// exports it
			ob_start(); ob_start();

			// call the script
			include_once($maintenance_path . $script . '.php');

			// exit, if the script doesn't explictly exit.  Will call the exit callback function
			exit;


		} else {
			echo wfMsg_MS('maintshell-noexist',  $maintenance_path .$scriptHTML);
		}
	}
	exit;
}
