/*
 * Minify Images.
 *
 * @package Boilderplate
 *
 * @since 1.0.0
 */

/* global files, gulp, handleErrors, imagemin, paths, plumber */

var images = [ files.images, '!' + files.svg ];

/**
 * Optimize images.
 *
 * @since 1.0.0
 */
gulp.task( 'imagemin', () =>
	gulp.src( images )
		.pipe( plumber({'errorHandler': handleErrors}) )
		.pipe( imagemin({
			'optimizationLevel': 5,
			'progressive': true,
			'interlaced': true
		}) )
		.pipe( gulp.dest( paths.images ) )
);
