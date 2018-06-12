var gulp = require('gulp');
var sass = require('gulp-sass');
var cleancss = require('gulp-clean-css');
var minify = require('gulp-minify');
var preprocess = require('gulp-preprocess');
var plumber = require('gulp-plumber');
var wait = require('gulp-wait');

gulp.task('styles', function() {
    gulp.src('src/scss/style-purple.scss').pipe(plumber()).pipe(wait(500)).pipe(sass({
        includePaths: []
    })).pipe(cleancss()).pipe(gulp.dest('css'));

    return gulp.src('src/scss/style-default.scss').pipe(plumber()).pipe(wait(500)).pipe(sass({
        includePaths: []
    })).pipe(cleancss()).pipe(gulp.dest('css'));
});

gulp.task('scripts', function() {
    return gulp.src('src/js/main.js').pipe(plumber()).pipe(preprocess()).pipe(minify({
        noSource: true,
        ext: {
            min: '.min.js'
        }
    })).pipe(gulp.dest('js'));
});

gulp.task('watch', ['build'], function() {
    gulp.watch('src/scss/*.scss', ['styles']);
    gulp.watch('src/js/*.js', ['scripts']);
});

gulp.task('build', ['styles', 'scripts']);
gulp.task('default', ['build']);