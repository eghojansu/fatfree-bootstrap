var gulp = require('gulp')
var browserSync = require('browser-sync').create()
var $ = require('gulp-load-plugins')({
    pattern: ['gulp-*', 'gulp.*', 'main-bower-files', 'stream-series']
})
var assetInjector = function(filepath) {
    if (filepath.slice(-4) === '.css') {
        return '<link rel="stylesheet" href="{{ \''+filepath.substr(1)+'\'|path }}">'
    }
    if (filepath.slice(-3) === '.js') {
        return '<script src="{{ \''+filepath.substr(1)+'\'|path }}"></script>'
    }
    // Use the default transform as fallback:
    return $.inject.transform.apply($.inject.transform, arguments);
}

/* configuration ----------------------------------------------------------- */
var browserSyncProxy = 'http://localhost/www/fa/fatfree-bootstrap'

/* tasks ------------------------------------------------------------------- */

gulp.task('inject-asset', function() {
    console.log('injecting asset...')
    var vendorStyleStream = gulp.src($.mainBowerFiles('**/*.css'))
        .pipe($.concat('vendor.css'))
        .pipe(gulp.dest('asset/css'))
    var vendorScriptStream = gulp.src($.mainBowerFiles('**/*.js'))
        .pipe($.concat('vendor.js'))
        .pipe(gulp.dest('asset/js'))
    var appStream = gulp.src([
        'asset/js/*.js',
        'asset/css/*.css',
        '!asset/**/vendor.{css,js}',
        ], {read: false})

    gulp.src('app/view/layout/*.html')
        .pipe($.inject($.streamSeries(vendorScriptStream, vendorStyleStream), {name:'bower', transform: assetInjector}))
        .pipe($.inject($.streamSeries(appStream), {transform: assetInjector}))
        .pipe(gulp.dest('app/view/layout'))
})

gulp.task('copy-fonts', function() {
    console.log('copying fonts...')
    gulp.src('bower_components/**/*.{eot,svg,ttf,woff,woff2}')
        .pipe($.flatten())
        .pipe(gulp.dest('asset/fonts'))
})

gulp.task('compile-sass', ['copy-fonts'], function() {
    console.log('compiling sass...')
    gulp.src('dev/sass/bootstrap.scss')
        .pipe($.sass({outputStyle: 'compressed'}))
        .pipe(gulp.dest('asset/css'))

    gulp.src('dev/sass/style.scss')
        .pipe($.sass({outputStyle: 'nested'}))
        .pipe(gulp.dest('asset/css'))
})

gulp.task('watch-changes', function() {
    console.log('watching changes...')
    browserSync.init({
        proxy: browserSyncProxy,
        browser: ["chrome"],
        ghostMode: false,
        notify: false
    })
    gulp.watch('dev/sass/**/*.scss', ['compile-sass'])
    gulp.watch('bower.json', ['inject-asset'])
    gulp.watch('app/view/**/*.html').on('change', browserSync.reload)
})

gulp.task('default', ['compile-sass', 'inject-asset', 'watch-changes'])
