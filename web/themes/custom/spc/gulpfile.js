const gulp = require("gulp");
const spritesmith = require("gulp.spritesmith");
const imagemin = require('gulp-imagemin');
const buffer = require('vinyl-buffer');
const imageminAdvpng = require('imagemin-advpng');
const imageminPngquant = require('imagemin-pngquant');

const { watch, dest, src } = require("gulp");
const sourcemaps = require("gulp-sourcemaps");
const if_ = require("gulp-if");
const less = require("gulp-less");
const isDev = process.env.NODE_ENV == "development";

//npx gulp sprite
gulp.task("sprite", () => {
  var spriteData = gulp.src("./img/sprite/sprite_src/*.*").pipe(
    spritesmith({
      imgName: "spc_sprite.png",
      imgPath: "/web/themes/custom/spc/img/sprite/spc_sprite.png?v="+(Math.random()*0xFFFFFF<<0).toString(16),
      cssName: "spc_sprite.css",
      padding: 20
    })
  );

  spriteData.img.pipe(buffer()).pipe(imagemin([
    imageminPngquant({
      strip: true,
      dithering: 0,
      quality: [0.1, 0.75]
    }),
    imageminAdvpng({optimizationLevel: 4}),
  ])).pipe(gulp.dest("./img/sprite/"));

  spriteData.css.pipe(gulp.dest("./css/sprite/"));
});

const build = () =>
  src("./less/spc.less")
    .pipe(if_(isDev, sourcemaps.init()))
    .pipe(less())
    .pipe(if_(isDev, sourcemaps.write()))
    .pipe(dest("./css"));
exports.watch = () =>
  watch("./less/**/*.less", { ignoreInitial: false }, build);

exports.build = build;
