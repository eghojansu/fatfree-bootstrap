var path = require('path');
var gulp = require('gulp');
var concat = require('gulp-concat');
var minify = require('gulp-minify');
var replace = require('gulp-replace');
var sass = require('gulp-sass');
var cleanCss = require('gulp-clean-css');
var flatten = require('gulp-flatten');
var postcss = require('gulp-postcss');
var autoprefixer = require('autoprefixer');
var browserSync = require('browser-sync').create();
var proxy = 'http://fal.dev/'+path.basename(__dirname)+'/dev/';
var cssUrlPattern = /url(?:\(['"]?)(?:.*?)([\w\-\.\?=#&]+?)(?:['"]?\))/g;
var config = require('./gulpfile-config.json');
var argv = process.argv.slice(2);
var env = 'dev';
for (var i = argv.length - 1; i >= 0; i--) {
    if (/^\-/.test(argv[i])) {
        env = argv[i].replace(/\-+/,'');
    }
}



gulp.task('vendor_scripts', function() {
    gulp.src(config.vendor_scripts.src)
        .pipe(concat(config.vendor_scripts.compiled))
        .pipe(minify({
            noSource: true,
            ext: {
                src:'-debug.js',
                min:'.js'
            }
        }))
        .pipe(gulp.dest(config.assets_dir[env]))
        .pipe(browserSync.stream())
    ;
});
gulp.task('vendor_styles', function() {
    var resourcePath = 'url("'+config.vendor_resources.compiled+'/$1")';
    gulp.src(config.vendor_styles.src)
        .pipe(replace(cssUrlPattern, resourcePath))
        .pipe(concat(config.vendor_styles.compiled))
        .pipe(cleanCss())
        .pipe(gulp.dest(config.assets_dir[env]))
        .pipe(browserSync.stream())
    ;
});
gulp.task('vendor_resources', function() {
    gulp.src(config.vendor_resources.src)
        .pipe(flatten())
        .pipe(gulp.dest(config.assets_dir[env]+'/'+config.vendor_resources.compiled))
    ;
});

gulp.task('scripts', function() {
    gulp.src(config.scripts.src)
        .pipe(minify({
            noSource: true,
            ext: {
                src:'-debug.js',
                min:'.js'
            }
        }))
        .pipe(gulp.dest(config.assets_dir[env]))
        .pipe(browserSync.stream())
    ;
});
gulp.task('styles', function() {
    var resourcePath = 'url("'+config.resources.compiled+'/$1")';
    gulp.src(config.styles.src)
        .pipe(sass({outputStyle: 'compressed'}).on('error', sass.logError))
        .pipe(replace(cssUrlPattern, resourcePath))
        .pipe(postcss([
            autoprefixer({
                "browsers": [
                  "Android 2.3",
                  "Android >= 4",
                  "Chrome >= 20",
                  "Firefox >= 24",
                  "Explorer >= 8",
                  "iOS >= 6",
                  "Opera >= 12",
                  "Safari >= 6"
                ]
            })
        ]))
        .pipe(gulp.dest(config.assets_dir[env]))
        .pipe(browserSync.stream())
    ;
});
gulp.task('resources', function() {
    gulp.src(config.resources.src)
        .pipe(flatten())
        .pipe(gulp.dest(config.assets_dir[env]+'/'+config.resources.compiled))
    ;
});

gulp.task('clear', function() {
    if (!config.assets_dir[env]) {
        return;
    }

    var exec = require('child_process').exec;
    exec('rm -r ' + config.assets_dir[env], function (err, stdout, stderr) {
      // your callback goes here
    });
});

gulp.task('build', ['clear','resources','styles','scripts','vendor_resources','vendor_styles','vendor_scripts']);

gulp.task('watch', ['clear','resources','styles','scripts','vendor_resources','vendor_styles','vendor_scripts'], function() {
    browserSync.init({
        proxy: proxy,
        online: false,
        ghostMode: false,
        open: false
    });

    gulp.watch(config.styles.src, ['styles']);
    gulp.watch(config.scripts.src, ['scripts']);
    gulp.watch(config.resources.src, ['resources']);
    gulp.watch(config.watch_reload).on('change', browserSync.reload);
});
gulp.task('default', ['watch']);
