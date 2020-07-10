/**
 * requires
 */
const uglify = require('gulp-uglify');
const rename = require('gulp-rename');
const changed = require('gulp-changed');

// require("@babel/polyfill");
const rollup = require('gulp-better-rollup');
const babel = require('rollup-plugin-babel');
const terser = require('rollup-plugin-terser').terser;

/**
 * Introduces
 */
const { task, src, dest, watch, series, parallel } = require('gulp');
/**
 * defines paths
 */
const paths = {
    javascript_design: {
        src: './dev/*.js',
        dest: './common/'
    }
};


/**
 * htdocs/resources/javascript/dev js build
 * @returns {*}
 */
const javascript_design = function () {
    return src(paths.javascript_design.src)
        .pipe(changed(paths.javascript_design.dest, { extension: '.js' }))
        .pipe(rollup({
            plugins: [babel({
                babelrc: false,
                plugins: ['@babel/plugin-proposal-class-properties']
            }),terser()]
        }, 'umd'))
        // .pipe(uglify())
        .pipe(rename(function (path) {
            path.basename = path.basename + '.min';
        }))
        .pipe(dest(paths.javascript_design.dest));
};


const watcher_design = async function () {
    watch(paths.javascript_design.src, javascript_design);
};

const design = series(parallel(watcher_design));

task('default', design);


// 输出所有任务
const show_tasks = series(parallel(function task () {
    console.log('global_unit_task');
}));
task('task', show_tasks);


