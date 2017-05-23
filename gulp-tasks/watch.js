/*
 * Watch.
 *
 * @package Boilderplate
 *
 * @since 1.0.0
 */

/* global files, gulp */

/**
 * Watch
 *
 * @since 1.0.0
 */
gulp.task( 'watch', () => {
  gulp.watch( files.sass, [ 'styles' ]);
  gulp.watch( files.concatScripts, [ 'scripts' ]);
  gulp.watch( files.images, [ 'imagemin' ]);
});
