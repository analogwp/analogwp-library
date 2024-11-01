const gulp = require( 'gulp' );
const run = require( 'gulp-run-command' ).default;
const checktextdomain = require( 'gulp-checktextdomain' );

gulp.task( 'build', run( 'npm run build' ) )
gulp.task( 'makePot', run( 'npm run makePot' ) );
gulp.task( 'convertPot2json', run('npm run convertPot2json' ) );

gulp.task( 'checktextdomain', ( done ) => {
	gulp
		.src( [ '**/*.php', '!build/**', '!languages/**', '!./inc/class-licensemanager.php' ] )
		.pipe( checktextdomain( {
			text_domain: 'ang',
			keywords: [
				'__:1,2d',
				'_e:1,2d',
				'_x:1,2c,3d',
				'esc_html__:1,2d',
				'esc_html_e:1,2d',
				'esc_html_x:1,2c,3d',
				'esc_attr__:1,2d',
				'esc_attr_e:1,2d',
				'esc_attr_x:1,2c,3d',
				'_ex:1,2c,3d',
				'_n:1,2,4d',
				'_nx:1,2,4c,5d',
				'_n_noop:1,2,3d',
				'_nx_noop:1,2,3c,4d',
			],
		} ) );

	done();
} );

gulp.task( 'translate', gulp.series(
	'checktextdomain',
	'makePot',
	'convertPot2json',
	function( done ) {
		done();
	} )
);

gulp.task( 'github-build', gulp.series(
	'checktextdomain',
	function( done ) {
		done();
	} )
);
