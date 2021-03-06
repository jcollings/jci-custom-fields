<?php
/**
 * Load WordPress test environment
 */

$path = '../../../../tests/phpunit/includes/bootstrap.php';

$GLOBALS['wp_tests_options'] = array(
	'active_plugins' => array( "jc-importer/jc-importer.php", "/jci-custom-fields/jci-custom-fields.php" ),
);

require_once '../jc-importer/tests/includes/factory.php';

if ( file_exists( $path ) ) {
	require_once $path;
} else {
	exit( "Couldn't find path to wordpress-tests/bootstrap.php\n" );
}
