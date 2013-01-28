<?php
/**
 * Special page interface for Maintenance Shell
 */
class MaintenanceShell extends SpecialPage {
	function __construct() {
		parent::__construct( 'MaintenanceShell','maintenanceshell' );
		wfLoadExtensionMessages('MaintenanceShell');
	}
 
	function execute( $par ) 
	{
		global $wgRequest, $wgOut, $IP, $wgUser, $wgMaintShellPermissions;
 		
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
 		
 		$token = $wgUser->getToken();
 		
		
		if ($wgMaintShellPermissions === 0)
		{	
			$wgOut->addHTML(wfMsg('maintshell-installfail2'));
				return;
		}
		
		$scriptNameHTML = self::HTMLescape($wgRequest->getText('script'));
		$commandlineHTML = self::HTMLescape($wgRequest->getText('commandline'));
		
		$maintenance_path = $IP.'/maintenance/';
		$str = '<b>' . wfMsg('maintshell-warning'). '</b>' .
			"<br /><br /><form action='$_SERVER[SCRIPT_NAME]' method='post'>" .
			'<input name="title" value="Special:MaintenanceShell" type="hidden">' .
			"<table><tr><td><b>". wfMsg('maintshell-scriptname') ."</b>:</td><td> <input name='script' value='" . $scriptNameHTML .  "'/>.php</td></tr>" .			
			'<tr><td><b>'. wfMsg('maintshell-commandline') .'</b>:</td><td> <input name="commandline" size="70" value="' .  $commandlineHTML .'"/></td></tr>' .			
			'</table><br /><input name="submit" type="submit" value="' . wfMsg('maintshell-runscript') . '"/>'
			.'<input type="hidden" name="token" value="' .$token . '" /> </form>'. wfMsg('maintshell-links') .'<hr />';
		 
			
		$wgOut->addHTML($str);
						
		$files = array_diff( scandir($maintenance_path ), Array( ".", ".." ) );
		if (count($files))
		{	$wgOut->addHTML('<b>'. wfMsg('maintshell-available') .'</b><pre><table cellpadding=5><tr>');
			$count = 0;
			foreach ($files as $v)
			{	if (($arr = pathinfo($v)) && array_key_exists('extension', $arr) && ($arr['extension'] == 'php') && (strpos($v, '.inc') === false))			
				{	$v = str_replace('.php','',$v);
					$wgOut->addHTML( "<td><a href='$_SERVER[SCRIPT_NAME]?title=". wfMsg('maintshell-pagename') ."&script=$v'>$v</a></td>");		
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

	/**
	 * aggressively escape everything besides letters and numbers
	 */
	static function HTMLescape($string)
	{
			return preg_replace_callback('%([^A-Za-z0-9 ])%', 	
		         create_function('$matches', 'return "&#" . ord($matches[1]) .";" ;'),
		         $string);	
}
}
