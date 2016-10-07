var gulp = require('gulp')
var $ = require('gulp-load-plugins')()
var path = (function() {
    var p = {}
    var mf = function(base, suffix) {
        if (suffix && Array.isArray(suffix)) {
            var sources = []
            for (var i = suffix.length - 1; i >= 0; i--) {
                sources.push(base + suffix[i])
            }

            return sources
        }

        return base + (suffix || '')
    }

    p.sass = function(suffix) {
        var base = 'dev/sass/'

        return mf(base, suffix)
    }

    p.dest = function(suffix) {
        var base = 'asset/'

        return mf(base, suffix)
    }

    return p
})()

gulp.task('compile-sass', function() {
    gulp.src(path.sass('bootstrap.scss'))
        .pipe($.sass({outputStyle: 'compressed'}))
        .pipe(gulp.dest(path.dest('css')))
    gulp.src(path.sass('style.scss'))
        .pipe($.sass({outputStyle: 'nested'}))
        .pipe(gulp.dest(path.dest('css')))
})

gulp.task('watch-changes', function() {
    gulp.watch(path.sass('**/*.scss'), ['compile-sass'])
})

gulp.task('default', ['compile-sass', 'watch-changes'])
