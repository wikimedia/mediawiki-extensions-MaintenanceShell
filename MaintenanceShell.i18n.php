<?php
$messages = array();

/** English
 * @author Andrew Fitzgerald
 */
$messages['en'] = array(
	'maintenanceshell'         => 'Maintenance Shell',
	'maintenanceshell-desc'    => 'Wiki interface for maintenance scripts',

	# Special:ListGroupRights
	'right-maintenanceshell'    => 'Execute maintenance scripts',
	# FormSpecialPage "You don't have access to {$action}, for the following reason:"
	'action-maintenanceshell'   => 'the maintenance shell',

	# Special:MaintenanceShell
	'maintenanceshell-legend'            => 'Maintenance Shell',
	'maintenanceshell-text'              => "'''Warning:''' Use these scripts with care.  They are intended for developers only.
* [//www.mediawiki.org/wiki/Manual:Maintenance_scripts Manual:Maintenance scripts]
* [//www.mediawiki.org/wiki/Extension:MaintenanceShell Extension:MaintenanceShell]
",
	'maintenanceshell-return'            => 'Return to [[{{#special:maintenanceshell}}]].',
	'maintenanceshell-error-scriptname'  => 'Script not found',
	'maintenanceshell-error-rawsubmit'   => 'For security reasons, this page requires javascript to be enabled.',
	'maintenanceshell-available'         => 'Available maintenance scripts:',
	'maintenanceshell-field-scriptname'  => 'Script name:',
	'maintenanceshell-field-args'        => 'Command line options:',
	'maintenanceshell-field-submit'      => 'Run script',
);


/** German (Deutsch)
 * @author kghbln
 */
$messages['de'] = array(
	'maintenanceshell' => 'Wartungs-Shell',
	'maintenanceshell-desc' => 'Erg&auml;nzt eine [[Special:MaintenanceShell|Spezialseite]] mit hilfreichen Links zu Wartungsskripten f&uuml;r die Systemadministration.',
	'maintenanceshell-pagename' => 'Special:Wartungs-Shell',
	'right-maintenanceshell' => 'Wartungsskripte &uuml;ber die Wartungs-Shell ausf&uuml;hren.',
	'maintenanceshell-warning' => "'''Achtung:''' Setze diese Skripte sorgf&auml;ltig ein. Dies wird zudem nur Systemadministratoren und fortgeschrittenen Nutzern empfohlen.",
	'maintenanceshell-return' => 'R&uuml;ckkehr zur Wartungs-Shell',
	'maintenanceshell-noexist' => 'Das Skript ist nicht vorhanden',
	'maintenanceshell-available' => 'Verf&uuml;gbare Wartungsskripte:',
	'maintenanceshell-field-script' => 'Name des Skrips:',
	'maintenanceshell-commandline' => 'Zus&auml;tzliche Kommandos:',
	'maintenanceshell-field-args' => 'Skript ausf&uuml;hren',
);

/** German (formal address) (Deutsch (Sie-Form))
 * @author kghbln
 */
$messages['de-formal'] = array(
	'maintenanceshell-warning'       => "'''Achtung:''' Setzen Sie diese Skripte sorgf&auml;ltig ein. Dies wird zudem nur Systemadministratoren und fortgeschrittenen Nutzern empfohlen.",
);
