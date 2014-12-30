var concat = require('gulp-concat'),
    crypto = require('crypto'),
    gulp = require('gulp'),
    rename = require('gulp-rename'),
    rimraf = require('gulp-rimraf'),
    runSequence = require('run-sequence'),
    sass = require('gulp-ruby-sass'),
    source = require('vinyl-source-stream'),
    streamify = require('gulp-streamify');

var basedirs = {
    src: 'assets/src',
    build: 'assets/build'
};

// SOURCES CONFIG
var sources = {
    scripts: {
        app: [
            basedirs.src + '/js/**/*.js'
        ],
        watch: [basedirs.src + '/js/*.js', basedirs.src + '/js/**/*.js']
    },
    styles: {
        app: [
            basedirs.src + '/scss/**/*.scss'
        ],
        watch: [basedirs.src + '/scss/*.scss', basedirs.src + '/scss/**/*.scss']
    }
};

// BUILD TARGET CONFIG
var build = {
    scripts: {
        app: {
            dir: basedirs.build + '/js',
            main: 'app.js'
        }
    },
    styles: {
        app: {
            dir: basedirs.build + '/css',
            main: 'app.css'
        }
    }
};

//---------------
// TASKS
//---------------

// JS
gulp.task('scripts:app', function() {
    return gulp.src(sources.scripts.app)
        .pipe(concat(build.scripts.app.main))
        .pipe(gulp.dest(build.scripts.app.dir));
});

// CSS
gulp.task('styles:app', function() {
    return gulp.src(sources.styles.app)
        .pipe(sass({
            bundleExec: true,
            container: 'gulp-ruby-sass-' + crypto.createHash('md5')
                .update(__dirname)
                .digest('hex'),
            require: ['bootstrap-sass'],
            'sourcemap=none': true
        }))
        .on('error', function(err) {
            console.log(err.message);
        })
        .pipe(concat(build.styles.app.main))
        .pipe(gulp.dest(build.styles.app.dir));
});

gulp.task('clean', function() {
    return gulp.src(basedirs.build, { read: false })
        .pipe(rimraf());
});

gulp.task('watch', ['build'], function() {
    gulp.watch(sources.scripts.watch, ['scripts:app']);
    gulp.watch(sources.styles.watch, ['styles:app']);
});

gulp.task('build', function(callback) {
    runSequence(
        'clean', [
            'scripts:app',
            'styles:app'
        ],
        callback
    );
});

gulp.task('default', ['build']);
