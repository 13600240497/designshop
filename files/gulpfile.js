/**
 * requires
 */
var path = require('path')
var less = require('gulp-less')
var cleanCSS = require('gulp-clean-css')
var uglify = require('gulp-uglify')
var rename = require('gulp-rename')
var changed = require('gulp-changed')
var autoprefixer = require('gulp-autoprefixer')
var concat = require('gulp-concat')
const eslint = require('gulp-eslint');

// require("@babel/polyfill");
var babel = require('gulp-babel')

/**
 * Introduces
 */
var { task, src, dest, watch, series, parallel } = require('gulp')
/**
 * defines paths
 */
var paths = {
  stylesheet: {
    src: 'parts/ui/**/index.less',
    dest: 'parts/ui/'
  },
  javascript: {
    src: 'parts/ui/**/index.dev.js',
    dest: 'parts/ui/'
  }
}
var stylesheet = function () {
  return src(paths.stylesheet.src)
    .pipe(less({
      path: [ path.join(__dirname, 'less', 'includes') ]
    }))
    .pipe(cleanCSS())
    .pipe(changed(paths.stylesheet.dest, {extension: '.css'}))
    .pipe(dest(paths.stylesheet.dest))
    .pipe(autoprefixer({
            cascade: true, //是否美化属性值 默认：true 像这样：
            remove:true //是否去掉不必要的前缀 默认：true
        }))
      .pipe(dest(paths.stylesheet.dest));
}

var javascript = function () {
  return src(paths.javascript.src)
    .pipe(babel({
        babelrc: false,
        presets: ["env"],
        plugins: []
    }))  
    .pipe(uglify())
    .pipe(rename(function (path)  {
        path.basename = path.basename.split('.')[0];
    }))
    .pipe(changed(paths.javascript.dest, {extension: '.js'}))
    .pipe(dest(paths.javascript.dest))
}

var watcher = function () {
  watch(paths.stylesheet.src, stylesheet)
  watch(paths.javascript.src, javascript)
}

var build = series(parallel(watcher))

task('default', build)


/**
 * 组件JS代码规范检查
 * @example gulp eslint
 */
task('eslint', () => {
  return src(['parts/ui/**/index.js'])
        // eslint() attaches the lint output to the "eslint" property
        // of the file object so it can be used by other modules.
        .pipe(eslint({
          "envs": ["browser"],
          "rules": {
            "no-undef": 2
          },
          "globals":[
            "jQuery",
            "$",
            "window",
            "GEShopSiteCommon",
            "GESHOP_UTIL",
            "layui",
            "GESHOP_PLATFORM",
            "GESHOP_SITECODE",
            "GESHOP_STATIC",
            "GESHOP_LANG",
            "GESHOP_INTERFACE",
            "$LAB",
            "loadCss",
            "GS_GOODS_LAZY_FN",
            "Swiper",
            "GLOBAL",
            "gs_laytpl",
            "gbLogsss",
            "GESHOP_LANGUAGES",
            "GESHOP_PIPELINE",
            "COOKIESDIAMON",
            "GESHOP",
            "DOMAIN_LOGIN",
            "DOMAIN_CART",
            "HTTPS_LOGIN_DOMAIN",
            "GESHOP_HIGH_AMOUNT_LIST_LANGUAGES",
            "$rootPhone"
          ]
        }))
        // eslint.format() outputs the lint results to the console.
        // Alternatively use eslint.formatEach() (see Docs).
        .pipe(eslint.format())
        // To have the process exit with an error code (1) on
        // lint error, return the stream and pipe to failAfterError last.
        .pipe(eslint.failAfterError());
});





// 公共元素控件样式less文件合并打包
var global_unit_less_source = 'parts/components/default/pc/**/index.less';
var global_stlyesheets_dist = __dirname.replace('files', 'htdocs/frontend/geshop/stylesheets/');
var global_unit_less = function() {
    return src(global_unit_less_source)
    .pipe(less())
    .pipe(concat('geshop-components-default-pc.css'))
    .pipe(cleanCSS())
    .pipe(dest(global_stlyesheets_dist));
}
var global_unit_watcher = function() {
    watch(global_unit_less_source, global_unit_less)
}
var global_unit_task = series(parallel(global_unit_watcher))
task('global_unit_task', global_unit_task)


// 输出所有任务
var show_tasks = series(parallel(function task() {
    console.log('global_unit_task')
}))
task('task', show_tasks)


