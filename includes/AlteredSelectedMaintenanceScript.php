<?php

class AlteredSelectedMaintenanceScript extends SelectedMaintenanceScript {

	/**
	 * Copied from core Maintenance::setup.
	 *
	 * Overriding it to exclude these checks:
	 *
	 * - `ini_get( 'register_argc_argv' )`
	 *   > Disabled by default in many PHP configurations for Apache servers.
	 *
	 * - `$_SERVER['REQUEST_METHOD']`
	 *   > Because we're not actually on the command line. This can be simulated
	 *     using unset(), but since we're overriding this anyway, might as well remove it.
	 *
	 * - `define( 'MEDIAWIKI', true )`
	 *   > This would throw an E_NOTICE, since we're already in MediaWiki request context.
	 */
	public function setup() {
		global $wgCommandLineMode, $wgRequestTime;

		if ( ini_get( 'display_errors' ) ) {
			ini_set( 'display_errors', 'stderr' );
		}

		$this->loadParamsAndArgs();
		$this->maybeHelp();
		$this->adjustMemoryLimit();

		ini_set( 'max_execution_time', 0 );

		$wgRequestTime = microtime( true );
		$wgCommandLineMode = true;

		@ob_end_flush();

		$this->validateParamsAndArgs();
	}
}
