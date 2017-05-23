/*
 * Generate .po file for translatable text.
 *
 * @package Boilderplate
 *
 * @since 1.0.0
 */

/* global del, files, getPackageJson, gulp, handleErrors, plumber, sort, wpPot */

var pkg    = getPackageJson(),
		domain = pkg.name;

/**
 * Delete the theme's .pot before we create a new one.
 *
 * @since 1.0.
 */
gulp.task( 'cleanPot', () =>
	del([ 'languages/' + domain + '.pot' ])
);

/**
 * Scan the files and create a POT file.
 *
 * @since 1.0.0
 */
gulp.task( 'pot', [ 'cleanPot' ], () =>
	gulp.src( files.php )
		.pipe( plumber({'errorHandler': handleErrors}) )
		.pipe( sort() )
		.pipe( wpPot({
			'domain': domain,
			'package': domain
		}) )
		.pipe( gulp.dest( 'languages/' + domain + '.pot' ) )
);
