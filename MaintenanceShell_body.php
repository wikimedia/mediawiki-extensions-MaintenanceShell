<?php
class MaintenanceShell extends SpecialPage {
	function __construct() {
		parent::__construct( 'MaintenanceShell','maintenanceshell' );
		wfLoadExtensionMessages('MaintenanceShell');
	}
 
	function execute( $par ) 
	{
		global $wgRequest, $wgOut, $IP, $wgUser;
 		
 		# If user is blocked, s/he doesn't need to access this page
		if ( $wgUser->isBlocked() ) {
			$wgOut->blockedPage();
			return;
		}

		# Show a message if the database is in read-only mode
		if ( wfReadOnly() ) {
			$wgOut->readOnlyPage();
			return;
		}

		# If the user doesn't have the required 'maintenanceshell' permission, display an error
		if( !$wgUser->isAllowed( 'maintenanceshell' )) {
			$wgOut->permissionRequired( 'maintenanceshell' );
			return;
		}
 
		$this->setHeaders();
 		
 		$maintenance_path = $IP.'/maintenance/';

		$str = "<b>Warning: Use these scripts with care.  They are intended for administrators and other advanced users only.</b><br /><br /><form action='$_SERVER[SCRIPT_NAME]' method='get'>" .
			'<input name="title" value="Special:MaintenanceShell" type="hidden">' .
			"<table><tr><td><b>Script name</b>:</td><td> <input name='script' value='" . (array_key_exists('script', $_REQUEST) ? trim($_REQUEST['script']) : '').  "'/>.php</td></tr>" .			
			'<tr><td><b>Command line options</b>:</td><td> <input name="commandline" size="70" value="' .  (array_key_exists('commandline', $_REQUEST) ? trim($_REQUEST['commandline']) : '') .'"/></td></tr>' .			
			'</table><br /><input name="submit" type="submit" value="Run script"/></form><br /><hr />';
		
			
		$wgOut->addHTML($str);
						
		$files = array_diff( scandir($maintenance_path ), Array( ".", ".." ) );
		if (count($files))
		{	$wgOut->addHTML("<b>Available maintenance scripts:</b><pre><table cellpadding=5><tr>");
			$count = 0;
			foreach ($files as $v)
			{	if (($arr = pathinfo($v)) && ($arr['extension'] == 'php') && (strpos($v, '.inc') === false))
				{	$v = str_replace('.php','',$v);
					$wgOut->addHTML( "<td><a href='$_SERVER[SCRIPT_NAME]&title=Special:MaintenanceShell?script=$v'>$v</a></td>");		
					$count++;
					if ($count == 4)
					{ $wgOut->addHTML( "</tr><tr>");
						$count = 0;
					}
				}
			}
			$wgOut->addHTML("</tr></table>");
			
		}	 
		
		$wgOut->addHTML('</pre>');
		
	}
}

?>
