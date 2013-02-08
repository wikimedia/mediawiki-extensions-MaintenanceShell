<?php

/**
 * Special page interface for Maintenance Shell.
 *
 * TODO: FormSpecialPage implements a token protection.
 * That's good but ideally we'd use a custom token (Like FormAction allows).
 */
class SpecialMaintenanceShell extends FormSpecialPage {
	private $maintshellOutput = '';

	public function __construct() {
		parent::__construct( 'MaintenanceShell', 'maintenanceshell' );
		$out = $this->getOutput();
		$out->addModules( 'ext.maintenanceShell' );
	}

	public function execute( $par ) {
		$out = $this->getOutput();
		$out->addHTML( '<div class="mw-sp-maintenanceShell">' );
		parent::execute( $par );
		$out->addHTML( '</div>' );
	}

	/**
	 * @return Array
	 */
	protected function getFormFields() {
		global $wgMaintenanceShellPath;

		$files = array_map( 'basename', glob( $wgMaintenanceShellPath . '/*.php' ) );
		$options = array_combine( $files, $files );

		// Blacklist
		unset( $options['Maintenance.php'] );
		unset( $options['doMaintenance.php'] );

		// Add empty value to top of the list
		// Note: HTMLForm automatically considers '' to be an invalid
		// value if required:true is set.
		$options[''] = '';

		ksort( $options );

		return array(
			'Script' => array(
				'type' => 'select',
				'label-message' => 'maintenanceshell-field-scriptname',
				'tabindex' => '1',
				'size' => '45',
				'required' => true,
				'options' => $options,
				'default' => '',
			),
			'Arguments' => array(
				'type' => 'text',
				'label-message' => 'maintenanceshell-field-args',
				'tabindex' => '1',
				'size' => '70',
			)
		);
	}

	protected function alterForm( HTMLForm $form ) {
		$form->setSubmitTextMsg( 'maintenanceshell-field-submit' );
		/* Control field to prevent accidential submissions
		 * in browsers without javascript support.
		 *
		 * We require the javascript module to have successfully
		 * done its work, as otherwise there is a potential of the
		 * form being submitted directly which in IE can cause
		 * the output to be interpretted as html.
		 */
		$form->addHiddenField( 'controlfield', '0', array( 'class' => 'mw-sp-maintenanceShell-controlfield' ) );
	}

	/**
	 * After form input is validated, we run the maintenance script.
	 * This is before the page output is generated.
	 *
	 * @param Array $data
	 * @return Bool|Array true on success, array of errors on failure
	 */
	public function onSubmit( Array $data ) {
		global $wgMaintenanceShellPath;

		$filePath = $wgMaintenanceShellPath . '/' . $data['Script'];
		// Verify one more time, in case of race condition or forged submission.
		if ( !is_file( $filePath ) ) {
			return array( 'maintenanceshell-error-scriptname' );
		}

		if ( $this->getRequest()->getVal('controlfield') !== '1' ) {
			return array( 'maintenanceshell-error-rawsubmit' );
		}

		$this->mainshellExec( $filePath, $data['Arguments'] );

		#$this->maintshellPrompt = getcwd() . '$ php ' . $data['Script'] . ' ' . $data['Arguments'];
		#$this->maintshellOutput = $this->mainshellExec( $filePath, $data['Arguments'] );
		#return true;
	}


	public function onSuccess() {
		// Never happens because onSubmit callback to mainshellExec
		// will exit before. We handle this in the front-end instead
		// because maintenance scripts are hard to catch the output of.
		// We instead let the script output and exit, but we'lll make the
		// request itself go through AJAX, so the javascript module can
		// safely catch the output and present it to the user.
	}

	/**
	 * Execute the script and echo output
	 * to the browser as plain text.
	 */
	private function mainshellExec( $filePath, $arguments ) {
		global $wgMaintenanceShellPath, $IP, $wgTitle;

		// Replace placeholders
		$arguments = str_ireplace( '{{root}}', $_SERVER['DOCUMENT_ROOT'], $arguments );

		// Simulate working directory
		chdir( $wgMaintenanceShellPath );

		// Build $argv and $argc
		$arguments = basename( $filePath ) . ' ' . $arguments;
		$parser = new MaintenanceShellArgumentsParser( $arguments );
		$GLOBALS['argv'] = $parser->getArgv();
		$GLOBALS['argc'] = $parser->getArgc();

		// Output plain text header to avoid output being misintepreted as html
		header( 'Content-Type: text/plain; charset=utf-8' );

		require_once( $filePath );

		// We could eval the entire extension, but lets only eval the part
		// we need, namely the variable class extension.
		eval( "class SelectedMaintenanceScript extends $maintClass {}" );

		// We need some alterations to the maintenance class to make it
		// work in a web request context.
		require_once( __DIR__ . '/AlteredSelectedMaintenanceScript.php' );

		// "DoMaintenance" (loaded from the maintenance script file) will have refused
		// to run the script, as Maintenance::shouldExecute returns false because
		// we're not on the command line and not in the global context.
		// So, reverse-engineer its logic here, using our altered version of the class.
		if ( !Maintenance::shouldExecute() ) {
			$maintenance = new AlteredSelectedMaintenanceScript();
			$maintenance->setup();
			// Already handled by our current request context:
			# require( $maintenance->loadSettings() );
			# AdminSettings.php
			# Setup.php
			$maintenance->finalSetup();
			$wgTitle = null;
			try {
				$maintenance->execute();
				$maintenance->globals();
			} catch ( MWException $mwe ) {
				echo $mwe->getText();
			}
		}

		// If the script doesn't explictly exit, we'll exit anyway
		exit;
	}
}
