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

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'MaintenanceShell' );
	wfWarn(
		'Deprecated PHP entry point used for MaintenanceShell extension. Please use wfLoadExtension instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
} else {
	die( 'This version of the MaintenanceShell extension requires MediaWiki 1.25+' );
}
