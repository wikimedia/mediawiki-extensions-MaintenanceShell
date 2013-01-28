<?php

session_start();
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
	'version' => '0.1.0',
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
		
		// catch operations before wiki does anything, so we can act like we're coming from the command line
		
		if (array_key_exists('commandline', $_REQUEST) && array_key_exists('title', $_REQUEST) && ($_REQUEST['title'] = 'Special:MaintenanceShell'))
		{	$head_redirect = 'Location: ' . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'] . '?title=Special:MaintenanceShell';
						
			if ($_REQUEST['commandline'] == 'set_password')
			{	file_put_contents($IP.'/extensions/MaintenanceShell/.password', md5($_REQUEST['pword']));
				header($head_redirect);
				exit;
			}
			
			if (!file_exists($IP.'/extensions/MaintenanceShell/.password') || !array_key_exists('pword',$_REQUEST) ||
			md5($_REQUEST['pword']) != file_get_contents($IP.'/extensions/MaintenanceShell/.password'))			 
			{					
				header($head_redirect.'&badpass=true&script=' . (array_key_exists('script', $_REQUEST) ? $_REQUEST['script'] :'none'));
				exit;
			}
			
			if (array_key_exists('pword',$_REQUEST))
			{	$_SESSION['MaintenanceShell'] = $_REQUEST['pword'];
			}
			 
			 
			echo '<a href="' . $_SERVER['SCRIPT_NAME'] . '&title=Special:MaintenanceShell">Return to the Maintenance Shell</a>';
			if (array_key_exists('script', $_REQUEST) && (trim($_REQUEST['script'] == ''))) 
			{	echo ' | <a href="' . $_SERVER['SCRIPT_NAME'] . '?title=Special:MaintenanceShell&set_password=true">Change shell password</a>';
			}
			$maintenance_path = $IP . "/maintenance/";
			
		
			$_REQUEST['commandline'] = str_replace('{{root}}', $_SERVER['DOCUMENT_ROOT'], $_REQUEST['commandline']);	
			
			// make commandLine.inc think we're coming from the command line
			unset($_SERVER['REQUEST_METHOD']);
			$args = explode(' ',$_REQUEST['commandline']);
			$argv = array(0=>'');
			$argv = array_merge($argv,$args);
			$argc = count($argv);
			
			if (array_key_exists('script', $_REQUEST) and (($script = trim($_REQUEST['script'])) !== ''))
			{	
				$script = str_replace(array('.','/'), '', $script);
					
				$argc = count ($argv);
				if (file_exists(trim($maintenance_path.$script.".php")))
				{	echo "<pre><hr />$ php ".$script . ".php " . implode($argv, ' ') . "\n\n";
					
								
					include_once($maintenance_path.$script.".php");
					
					echo "<hr /></pre>";
					exit;
				}
				else
				{	echo "Script '".$maintenance_path .$script."' does not exist!";
					
				}
			} 
			exit;
		}
?>
