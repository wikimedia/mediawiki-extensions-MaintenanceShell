<?php
/**
 * Hooks for MaintenanceShell extension
 *
 * @file
 * @ingroup Extensions
 */

class MaintenanceShellHooks {

	/**
	 * Set default values.
	 * Set from this hook so that core Setup and LocaLSettings apply first.
	 */
	public static function onSetup() {
		global $wgMaintenanceShellPath, $IP;

		if ( $wgMaintenanceShellPath === false ) {
			$wgMaintenanceShellPath = $IP . '/maintenance';
		}
	}

	public static function onUnitTestsList( Array &$files ) {
		$files[] = __DIR__ . '/tests/MaintenanceShellArgumentsParserTest.php';
	}
}
