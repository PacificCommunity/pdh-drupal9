const { watch, dest, src } = require("gulp");
const sourcemaps = require("gulp-sourcemaps");
const if_ = require("gulp-if");
const less = require("gulp-less");

const isDev = process.env.NODE_ENV == "development";

const build = () =>
  src("./less/spc.less")
    .pipe(if_(isDev, sourcemaps.init()))
    .pipe(less())
    .pipe(if_(isDev, sourcemaps.write()))
    .pipe(dest("./css"));
exports.watch = () =>
  watch("./less/**/*.less", { ignoreInitial: false }, build);

exports.build = build;
