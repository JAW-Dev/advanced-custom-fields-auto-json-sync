/*
 * Gulp Tasks.
 *
 * @package Boilderplate
 *
 * @since 1.0.0
 */

/* global require */
/* eslint no-undef: 0 */
/* eslint no-unused-expressions: 0 */

var requireDir   = require( 'require-dir' );

// Declare global variables for the gulp tasks.
args         = require( 'yargs' ).argv,
autoprefixer = require( 'autoprefixer' );
babel        = require( 'gulp-babel' );
bump         = require( 'gulp-bump' );
concat       = require( 'gulp-concat' );
cssnano      = require( 'gulp-cssnano' );
del          = require( 'del' );
fs           = require( 'fs' );
gulp         = require( 'gulp' );
gulpUtil     = require( 'gulp-util' );
imagemin     = require( 'gulp-imagemin' );
mqpacker     = require( 'css-mqpacker' );
notify       = require( 'gulp-notify' );
notify       = require( 'gulp-notify' );
plumber      = require( 'gulp-plumber' );
postcss      = require( 'gulp-postcss' );
rename       = require( 'gulp-rename' );
replace      = require( 'gulp-replace' );
sass         = require( 'gulp-sass' );
sassLint     = require( 'gulp-sass-lint' );
sort         = require( 'gulp-sort' );
sourcemaps   = require( 'gulp-sourcemaps' );
wpPot        = require( 'gulp-wp-pot' );
uglify       = require( 'gulp-uglify' ),
paths        = {
  'styles': 'assets/styles',
  'images': 'assets/images',
  'scripts': 'assets/scripts',
  'sass': 'assets/styles/sass'
},
files = {
	'css': paths.styles + '/*.css',
	'cssmin': paths.styles + '/*.min.css',
	'concatScripts': paths.scripts + '/concat/*.js',
  'html': [ './*.html', './**/*.html' ],
	'images': paths.images + '/*',
  'svg': paths.images + '/*.svg',
	'js': paths.scripts + '/*.js',
  'jsmin': paths.scripts + '/*.min.js',
  'php': [ './*.php', './**/*.php' ],
	'sass': paths.sass + '/**/*.scss',
  'styles': paths.styles + '/style.css'
},
getPackageJson = () => {
  return JSON.parse( fs.readFileSync( './package.json', 'utf8' ) );
};
handleErrors = () => {
	var args = Array.prototype.slice.call( arguments );
	notify.onError({
		'title': 'Task Failed [<%= error.message %>',
		'message': 'See console.',
		'sound': 'Sosumi'
	}).apply( this, args );
	gutil.beep();
	this.emit( 'end' );
};

// Require the gulp tasks.
requireDir( './gulp-tasks', { recurse: true });

gulp.task( 'default', [ 'scripts', 'styles', 'imagemin', 'bump', 'pot' ]);
