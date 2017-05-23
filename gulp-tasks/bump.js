/*
 * Bump Project Version.
 *
 * 1. gulp bump                    : Bumps the package.json and bower.json to the next minor revision.
 * 2. gulp bump --version 1.1.1    : Bumps/sets the package.json and bower.json to the specified revision.
 * 3. gulp bump --type major       : Bumps to 1.0.0.
 *    gulp bump --type minor       : Bumps to 0.1.0.
 *    gulp bump --type patch       : Bumps to 0.0.2.
 *    gulp bump --type prerelease  : Bumps to 0.0.1-2.
 *
 * @package Boilderplate
 *
 * @since 1.0.0
 */

/* global args, bump, getPackageJson, gulp, handleErrors, plumber, replace */

/**
 * package.json version bump.
 *
 * @since 1.0.0
 */
gulp.task( 'packageBump', () => {

  var type = args.type,
    version = args.version,
    options = {};

  if ( version ) {
    options.version = version;
  } else {
    options.type = type;
  }

  return gulp.src([ './package.json' ])
    .pipe( plumber({'errorHandler': handleErrors}) )
    .pipe( bump( options ) )
    .pipe( gulp.dest( './' ) );
});

/**
 * All files version bump.
 *
 * @since 1.0.0
 */
gulp.task( 'bump', [ 'packageBump' ], () => {
  var pkg = getPackageJson(),
    filePaths = [
      '!node_modules/',
      '!node_modules/**',
      '!Gulpfile.js',
      '!package.json',
      '!./gulp-tasks/bump.js',
      './**/*'
    ];

gulp.src( filePaths, { base: './', dot: false })
  .pipe( plumber({'errorHandler': handleErrors}) )
  .pipe( replace( /@since[ \t]+NEXT/g, '@since ' + pkg.version ) )
  .pipe( replace( /@version(.*)/g, '@version ' + pkg.version ) )
  .pipe( gulp.dest( './' ) );
});
