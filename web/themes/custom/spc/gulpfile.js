const { watch, dest, src } = require('gulp');
const sourcemaps = require('gulp-sourcemaps');
const less = require('gulp-less');

const build = () =>
  src('./less/spc.less')
    .pipe(sourcemaps.init())
    .pipe(less())
    .pipe(sourcemaps.write())
    .pipe(dest('./css'));
exports.watch = () =>
  watch('./less/**/*.less', { ignoreInitial: false }, build);

exports.build = build;
