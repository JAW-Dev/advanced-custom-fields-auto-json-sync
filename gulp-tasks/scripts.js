/*
 * Compile Scripts.
 *
 * @package Boilderplate
 *
 * @since 1.0.0
 */

/* global babel, concat, files, gulp, handleErrors, paths, plumber, sourcemaps, rename, uglify */

var js = [ files.js, '!' + files.jsmin ];

/**
 * Concatenate and transform JavaScript.
 *
 * @since 1.0.0
 */
gulp.task( 'concat', () =>
	gulp.src( files.concatScripts )
		.pipe( plumber({'errorHandler': handleErrors}) )
		.pipe( sourcemaps.init() )
		.pipe( babel({ presets: [ 'es2015' ] }) )
		.pipe( concat( 'index.js' ) )
		.pipe( sourcemaps.write() )
		.pipe( gulp.dest( 'assets/scripts' ) )
);

/**
  * Minify compiled JavaScript.
  *
  * @since 1.0.0
  */
gulp.task( 'uglify', [ 'concat' ], () =>
	gulp.src( js )
		.pipe( plumber({'errorHandler': handleErrors}) )
		.pipe( rename({ 'suffix': '.min' }) )
		.pipe( uglify({ 'mangle': false }) )
		.pipe( gulp.dest( paths.scripts ) )
);

/**
  * Compile JavaScript.
  *
  * @since 1.0.0
  */
gulp.task( 'scripts', [ 'uglify' ]);
