<?php

class MaintenanceShellArgumentsParser {
	private $raw;
	private $arguments;

	public function __construct( $str ) {
		$this->raw = strval( $str );
	}

	/**
	 * Get the supposed-be value of $argv given a string that
	 * would be written literally on the command line.
	 * This basically simulates the bash intepreter
	 * and (where relevant) PHPs specific perception of it
	 * as exposed in $arv. See also <http://php.net/argv/>.
	 *
	 * @return Array
	 */
	public function getArgv() {
		return $this->parseArguments();
	}

	public function getArgc() {
		return count( $this->parseArguments() );
	}

	private function parseArguments() {
		if ( $this->arguments !== null ) {
			return $this->arguments;
		}

		// Trim leading whitespace (not after backslash escape or in quotes)
		$chars = ltrim( $this->raw );

		// Stable results
		$values = array();

		// Continuous state
		$value = false;
		$inSingleWrap = false;
		$inDoubleWrap = false;
		$escapeNext = false;

		// Helper variables
		$inEscape = false;
		$inWrap = false;

		$chars_len = strlen( $chars );
		for ( $i = 0; $i < $chars_len; $i++ ) {
			$char = $chars[$i];

			$inEscape = $escapeNext;
			$escapeNext = false;
			$inWrap = $inSingleWrap || $inDoubleWrap;

			if ( $char === '\\' ) {
				// Plain or wrapped
				if ( !$inWrap ) {
					if ( !$inEscape ) {
						$escapeNext = true;
						// Beginning of a plain value of which the first character
						// (next iteration) will be escaped.
						if ( $value === false ) {
							$value = '';
						}
					} else {
						if ( $value === false ) {
							// XXX: Is it even possible to be in escape with value being false?
							$value = $char;
						} else {
							$value .= $char;
						}
					}
				// Wrapped
				} else {
					if ( $value === false ) {
						$value = $char;
					} else {
						$value .= $char;
					}
				}

			} elseif ( $char === '"' ) {
				// Plain: Begin wrap
				if ( !$inWrap ) {
					$inDoubleWrap = true;

				// Double wrap: End of wrap
				} elseif ( $inDoubleWrap ) {
					$inDoubleWrap = false;

				// Single wrap: Literal value
				} elseif ( $inSingleWrap ) {
					$value .= $char;
				}
			} elseif ( $char === "'" ) {
				// Plain: Begin wrap
				if ( !$inWrap ) {
					$inSingleWrap = true;

				// Single wrap: End of wrap
				} elseif ( $inSingleWrap ) {
					$inSingleWrap = false;

				// Double wrap: Literal value
				} elseif ( $inDoubleWrap ) {
					$value .= $char;
				}
			} elseif ( $char === ' ' ) {
				// Plain
				if ( !$inWrap ) {
					if ( !$inEscape ) {
						if ( $value !== false ) {
							array_push( $values, $value );
							$value = false;
						}
						// else: do nothing.
						// We're in plain context with no escape and no value yet.
						// Additional separator spaces are just ignored.
					} else {
						if ( $value === false ) {
							$value = $char;
						} else {
							$value .= $char;
						}
					}
				// Wrapped
				} else {
					// XXX: Should escaping be considered here?
					if ( $value === false ) {
						$value = $char;
					} else {
						$value .= $char;
					}
				}
			} else {
				if ( $value === false ) {
					$value = $char;
				} else {
					$value .= $char;
				}
			}
		}

		// If there was no trailing space, make sure we push in the
		// last value as well.
		if ( $value !== false ) {
			array_push( $values, $value );
		}

		$this->arguments = $values;
		return $this->arguments;
	}

}
