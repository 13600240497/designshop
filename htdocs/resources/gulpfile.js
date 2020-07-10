'use strict'
var gulp = require('gulp'),
	autoprefixer = require('autoprefixer'),
	cssnext = require('postcss-cssnext'),
	postcss = require('gulp-postcss'),
	px2rem = require('postcss-px2rem'),
	cssnano = require('cssnano'),
	nested = require('postcss-nested'),
	simplevars = require('postcss-simple-vars'),
	postimport = require('postcss-import')

var $ = require('gulp-load-plugins')(),
	merge = require('merge-stream'),
	globby = require('globby'),
	glob = require('glob')

var baseUrl = './sites/',
	PATH = {
		minjs: './minjs/',
		js: './js/',
	}

process.env.DISABLE_NOTIFIER = true


/* m端 整体scss */
gulp.task('scssResource', function () {
	var mProcessors = [
		cssnext({ warnForDuplicates: false }),
		px2rem({ remUnit: 37.5 }),
		cssnano,
	]
	gulp.src(dev.scss + '/*.scss')
		.pipe($.sourcemaps.init())
		.pipe($.plumber())
		.pipe($.sass({
			outputStyle: 'expanded', // libsass doesn't support expanded yet
			precision: 10,
			includePaths: ['.'],
			onError: console.error.bind(console, 'Sass error:')
		}))
		// .pipe($.sourcemaps.write(''))
		.pipe($.sourcemaps.write({ includeContent: false }))
		.pipe($.sourcemaps.init({ loadMaps: true }))
		.pipe($.postcss(mProcessors))
		.pipe($.sourcemaps.write('.'))
		.pipe(gulp.dest(dev.resource))
		.pipe($.notify({ message: 'scss task complete' }))
})


/*
 *lessFolder 任务流
 *站点less
 */

gulp.task('lessFolder', function () {
	var processors = [
		autoprefixer,
		cssnext
	]
	var mProcessors = [
		autoprefixer,
		cssnext,
		px2rem({ remUnit: 37.5 })
	]
	glob(baseUrl + '*/', {}, function (er, files) {
		var tasks = files.map(function (element) {
			return gulp.src(element + '/less/*.less')
				.pipe($.plumber())
				// .pipe($.sourcemaps.init())
				.pipe($.less())
				// .pipe($.sourcemaps.write('.'))
				.pipe($.if(element.indexOf('-wap') !== -1, $.postcss(mProcessors), $.postcss(processors)))
				.pipe($.cleanCss({
					compatibility: 'ie8'
				}))
				.pipe(gulp.dest(element + 'css/'))
				.pipe($.notify({ message: 'less task complete' }))
		})
		return merge(tasks)
	})

})

/*
 *jsFolder 任务流
 *站点js
 */
gulp.task('jsFolder', function () {
	glob(baseUrl + '*/', {}, function (er, files) {
		var tasks = files.map(function (element) {
			return gulp.src(element + '/js/*.js')
				.pipe($.plumber())
				.pipe($.include())
				.pipe($.uglify())
				.pipe($.rename({
					suffix: '.min'
				}))
				.on('error', handle.Errors)
				.pipe(gulp.dest(element + PATH.minjs))
				.pipe($.notify({ message: 'less task complete' }))
		})
		return merge(tasks)
	})

})

// 处理器
const handle = {
	Errors: err => {
		console.log('-------------- ' + chalk.bold.red('~ Error ~') + ' ------------')
		console.log('message => ' + err.message)
		console.log('plugin => ' + chalk.bold.red(err.plugin))
		gulp.src(err.fileName)
			.pipe($.notify('Error => <%= file.relative %>\nLine => ' + err.lineNumber))
	},
	Success: event => {
		console.log('-------------- ' + chalk.bold.green(event.type) + ' ------------')
		console.log('srcPath  => ' + chalk.bold.blue(event.path))
	}
}



gulp.task('watch', function () {
	gulp.watch(baseUrl + '*/less/*.less', ['lessFolder'])
	gulp.watch(baseUrl + '*/js/*.js', ['jsFolder'])
	// gulp.watch(baseUrl + '*/*/*.scss', ['scssFolder'])
	// gulp.watch(dev.scss + '*.scss', ['scssResource'])
	// gulp.watch(dev.scss + '*.css', ['postcss'])
})

gulp.task('tpl', function () {
	gulp.watch('./style_scss/*.scss', ['scssResource'])
})
