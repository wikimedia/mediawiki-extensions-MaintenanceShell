<?php
/**
 * Maintenance Shell extension.
 * Adds a special page to provide access to maintenance scripts
 *
 * @file
 * @ingroup Extensions
 * @copyright 2009-2013 Andrew Fitzgerald <contact@swiftlytilting.com>
 * @license GNU GPL
 */

$wgExtensionCredits['specialpage'][] = array(
	'name' => 'MaintenanceShell',
	'author' => array(
		'[http://swiftlytilting.com Andrew Fitzgerald]',
		'Timo Tijhof',
	),
	'url' => 'https://www.mediawiki.org/wiki/Extension:MaintenanceShell',
	'description' => 'Adds a special page to provide access to maintenance scripts.',
	'descriptionmsg' => 'maintenanceshell-desc',
	'version' => '0.5.0',
);


/* Setup */

$dir = dirname( __FILE__ );

// Register files
$wgAutoloadClasses['MaintenanceShellHooks'] = $dir . '/MaintenanceShell.hooks.php';
$wgAutoloadClasses['SpecialMaintenanceShell'] = $dir . '/includes/SpecialMaintenanceShell.php';
$wgAutoloadClasses['MaintenanceShellArgumentsParser'] = $dir . '/includes/MaintenanceShellArgumentsParser.php';
$wgMessagesDirs['MaintenanceShell'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['MaintenanceShell'] = $dir . '/MaintenanceShell.i18n.php';
$wgExtensionMessagesFiles['MaintenanceShellAlias'] = $dir . '/MaintenanceShell.alias.php';

// Register special pages
$wgSpecialPages['MaintenanceShell'] = 'SpecialMaintenanceShell';
$wgSpecialPageGroups['MaintenanceShell'] = 'wiki';

// Register user rights
$wgAvailableRights[] = 'maintenanceshell';

// Register hooks
$wgExtensionFunctions[] = 'MaintenanceShellHooks::onSetup';
$wgHooks['UnitTestsList'][] = 'MaintenanceShellHooks::onUnitTestsList';

// Register modules
$wgResourceModules['ext.maintenanceShell'] = array(
	'scripts' => 'resources/ext.maintenanceShell.js',
	'styles' => 'resources/ext.maintenanceShell.css',
	'localBasePath' => $dir,
	'remoteExtPath' => 'MaintenanceShell',
	'dependencies' => 'jquery.spinner'
);

// Not granted to anyone by default.
// To grant to "developer" group, use:
// $wgGroupPermissions['developer']['maintenanceshell'] = true;
// Or create a new user group, use:
// $wgGroupPermissions['maintainer']['maintenanceshell'] = true;

/* Configuration */

$wgMaintenanceShellPath = false;
