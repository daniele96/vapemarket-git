<?php
/**
 * Parse OPML XML files and store in globals.
 *
 * @package WordPress
 * @subpackage Administration
 */

if ( defined('ABSPATH') === false )
	trigger_error("Absolute path not defined.", E_USER_NOTICE);

/**
 * @global string $opml
 */
$opml=null;

/**
 * XML callback function for the start of a new XML tag.
 *
 * @since 0.71
 * @access private
 *
 * @global array $names
 * @global array $urls
 * @global array $targets
 * @global array $descriptions
 * @global array $feeds
 *
 * @param mixed $parser XML Parser resource.
 * @param string $tagName XML element name.
 * @param array $attrs XML element attributes.
 */
function startElement( $tagName, $attrs,$names, $urls, $targets, $descriptions, $feeds) {
	 $names; $urls; $targets; $descriptions; $feeds;

	if ( 'OUTLINE' === $tagName ) {
		$name = '';
		if ( isset( $attrs['TEXT'] ) === true ) {
			$name = $attrs['TEXT'];
		}
		if ( isset( $attrs['TITLE'] ) === true ) {
			$name = $attrs['TITLE'];
		}
		$url = '';
		if ( isset( $attrs['URL'] ) === true ) {
			$url = $attrs['URL'];
		}
		if ( isset( $attrs['HTMLURL'] ) === true ) {
			$url = $attrs['HTMLURL'];
		}

		// Save the data away.
		$names[] = $name;
		$urls[] = $url;
		$targets[] = isset( $attrs['TARGET'] ) === true ? $attrs['TARGET'] :  '';
		$feeds[] = isset( $attrs['XMLURL'] ) === true ? $attrs['XMLURL'] :  '';
		$descriptions[] = isset( $attrs['DESCRIPTION'] ) === true ? $attrs['DESCRIPTION'] :  '';
	} // End if outline.
}

/**
 * XML callback function that is called at the end of a XML tag.
 *
 * @since 0.71
 * @access private
 *
 * @param mixed $parser XML Parser resource.
 * @param string $tagName XML tag name.
 */


// Create an XML parser
if ( function_exists( 'xml_parser_create' ) === false ) {
	trigger_error( __( "PHP's XML extension is not available. Please contact your hosting provider to enable PHP's XML extension." ) );
	wp_die( __( "PHP's XML extension is not available. Please contact your hosting provider to enable PHP's XML extension." ) );
}

$xml_parser = null;
$xml_parser = xml_parser_create();

// Set the functions to handle opening and closing tags
xml_set_element_handler($xml_parser, "startElement", "endElement");

if ( xml_parse( $xml_parser, $opml, true ) === false ) {
	printf(
		/* translators: 1: error message, 2: line number */
		__( 'XML Error: %1$s at line %2$s' ),
		xml_error_string( xml_get_error_code( $xml_parser ) ),
		xml_get_current_line_number( $xml_parser )
	);
}

// Free up memory used by the XML parser
xml_parser_free($xml_parser);
