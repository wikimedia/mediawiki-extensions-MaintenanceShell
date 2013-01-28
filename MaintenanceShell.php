<?php

# Alert the user that this is not a valid entry point to MediaWiki if they try to access the special pages file directly.
if (!defined('MEDIAWIKI')) {
        echo <<<EOT
To install MaintenanceShell, put the following line in LocalSettings.php:
require_once( "\$IP/extensions/MaintenanceShell/MaintenanceShellphp" );
EOT;
        exit( 1 );
}
 
$wgExtensionCredits['specialpage'][] = array(
	'name' => 'MaintenanceShell',
	'author' => 'SwiftlyTilting',
	'url' => 'http://www.mediawiki.org/wiki/Extension:MaintenanceShell',
	'description' => 'Adds a special page to provide access to maintenance scripts',
	'descriptionmsg' => 'maintenanceshell-desc',
	'version' => '0.2.1',
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
		
$wgMaintShellPermissions = 0;
foreach ($wgGroupPermissions as $v)
{ $wgMaintShellPermissions += array_key_exists('maintenanceshell', $v) ? 1 : 0;
}
	


// catch operations before wiki does anything, so we can act like we're coming from the command line
if (array_key_exists('commandline', $_REQUEST) && array_key_exists('title', $_REQUEST) && ($_REQUEST['title'] = 'Special:MaintenanceShell'))
{  

	// first lets check to see if we're installed correctly

	if ($wgMaintShellPermissions === 0)
	{	echo "MaintenanceShell did not detect the user right <b>maintenanceshell</b> assigned to any group.<br /><br />" .
				"Please see <a href='http://www.mediawiki.org/wiki/Extension:MaintenanceShell'>the Extension:MaintenanceShell documentation</a> for more details.";
		exit;
	}
	
	// set up system to verify user permissions						
	require_once('./includes/Setup.php');			
	
	$head_redirect = 'Location: http://' .  $_SERVER['SERVER_NAME'] .                                
	               ($_SERVER['SERVER_PORT'] =="80" ? "":$_SERVER['SERVER_PORT']) . $_SERVER['SCRIPT_NAME'] . '?title=Special:MaintenanceShell';
	if ( $wgUser->isBlocked() || wfReadOnly() || !$wgUser->isAllowed( 'maintenanceshell' ) ) {
		header($head_redirect);
		return;
	}
				
	echo '<a href="' . $_SERVER['SCRIPT_NAME'] . '&title=Special:MaintenanceShell">Return to the Maintenance Shell</a>';
	
	$maintenance_path = $IP . "/maintenance/";	
	
	$_REQUEST['commandline'] = str_ireplace('{{root}}', $_SERVER['DOCUMENT_ROOT'], $_REQUEST['commandline']);	
	
	// make commandLine.inc think we're coming from the command line
	unset($_SERVER['REQUEST_METHOD']);
	$argv = explode(' ','0 '. $_REQUEST['commandline']);			
	$argc = count($argv);
	
	echo '<hr />';
	if (array_key_exists('script', $_REQUEST) and (($script = trim($_REQUEST['script'])) !== ''))
	{	
		$script = str_replace(array('.','/'), '', $script);
			
		$argc = count ($argv);
		if (file_exists(trim($maintenance_path.$script.".php")))
		{	echo "<div style='border:5px solid gray;background-color:black;color:green;padding:1em'>";
			echo "<pre style='white-space: -moz-pre-wrap;white-space: -pre-wrap;white-space: -o-pre-wrap;white-space: pre-wrap;word-wrap: break-word;'><b>";
			chdir(isset($wgMaintenanceShellDir) ? $wgMaintenanceShellDir : $maintenance_path);
			echo getcwd() . "$ php ". (isset($wgMaintenanceShellDir) ? $maintenance_path : '') .$script . ".php " . $_REQUEST['commandline'] . "</b>\n\n";
			
			function exit_callback($param = false)
			{							
				echo "</pre></div><hr />$param";
				exit;
			}
			register_shutdown_function('exit_callback');
			
			// call the script			
			include_once($maintenance_path.$script.".php");
			
			exit;
		}
		else
		{	echo "Script '".$maintenance_path .$script."' does not exist!";
		}
	} 
	exit;
}



$wgMaintenanceShellDir  = isset($wgMaintenanceShellDir) ? $wgMaintenanceShellDir :$maintenance_path;
?>
