<?php

/**
 * @covers MaintenanceShellArgumentsParser
 */
class MaintenanceShellArgumentsParserTest extends MediaWikiIntegrationTestCase {

	public static function provideArguments() {
		return array(
			array(
				'foo',
				array( 'foo' ),
				'One plain argument'
			),
			array(
				'foo bar',
				array( 'foo', 'bar' ),
				'Two plain arguments'
			),
			array(
				'foo bar baz',
				array( 'foo', 'bar', 'baz' ),
				'Three plain arguments'
			),
			array(
				'foo\ bar baz\ quux',
				array( 'foo bar', 'baz quux' ),
				'Plain argument with escaping'
			),
			array(
				'\ foo \ bar \ baz',
				array( ' foo', ' bar', ' baz' ),
				'Plain argument with escaping at start of value'
			),
			array(
				' \ foo    \ bar     \ baz ',
				array( ' foo', ' bar', ' baz' ),
				'Plain arguments with escaping and extra spacing'
			),
			array(
				"foo\nbar",
				array( "foo\nbar" ),
				'Plain argument with line break'
			),
			array(
				'"foo" "bar" "baz"',
				array( 'foo', 'bar', 'baz' ),
				'Double quoted arguments'
			),
			array(
				'" foo "  " bar "  " baz "',
				array( ' foo ', ' bar ', ' baz ' ),
				'Double quoted arguments with spacing'
			),
			array(
				'" foo\ "  " bar\ "  " baz\ "',
				array( ' foo\ ', ' bar\ ', ' baz\ ' ),
				'Double quoted arguments with spacing and backslashes'
			),
			array(
				// one more backslash for each one because of PHP's escaping.
				// Actual input: "foo\\" "\bar\"
				'"foo\\\\" "\bar\"',
				array( 'foo\\\\', '\bar\\' ),
				'Double quoted arguments with multple backslashes'
			),
			array(
				"\"foo\nbar\"",
				array( "foo\nbar" ),
				'Double quoted argument with line break'
			),
			array(
				"'foo' 'bar' 'baz'",
				array( 'foo', 'bar', 'baz' ),
				'Single quoted arguments'
			),
			array(
				"' foo '  ' bar '  ' baz '",
				array( ' foo ', ' bar ', ' baz ' ),
				'Single quoted arguments with spaces'
			),
			array(
				"'foo\nbar'",
				array( "foo\nbar" ),
				'Single quoted argument with line break'
			),
			array(
				'"foo""bar"\'quux\'',
				array( 'foobarquux' ),
				'Quotes only provide wrapping context, they are not value separators'
			),
			array(
				' path/to/something\ in/this\ directory.png  --baz="quux"  -abc="c++"  -d  e --  -f "' . "line\nbreak". '" ',
				array(
					'path/to/something in/this directory.png',
					'--baz=quux',
					'-abc=c++',
					'-d',
					'e',
					'--',
					'-f',
					"line\nbreak",
				),
				'Everything thrown together in one giant example (using double quotes)'
			),
		);
	}

	/**
	 * @dataProvider provideArguments
	 * @param string $input
	 * @param array $expected
	 * @param string $message
	 */
	public function testParseArguments( $input, Array $expected, $message ) {
		$parser = new MaintenanceShellArgumentsParser( $input );
		$this->assertEquals(
				$expected,
				$parser->getArgv(),
				$message
		);
	}
}
