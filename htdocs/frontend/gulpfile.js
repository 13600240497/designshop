/**
 * requires
 */
const path = require('path')
const less = require('gulp-less')
const cleanCSS = require('gulp-clean-css')
const uglify = require('gulp-uglify')
const changed = require('gulp-changed')
const rollup = require('gulp-better-rollup')
const babel = require('rollup-plugin-babel')

/**
 * Introduces
 */
const { task, src, dest, watch, series, parallel } = require('gulp')

/**
 * defines paths
 */
const paths = {
	geshop: {
		stylesheet: {
			src: './geshop/dev-stylesheets/*.less',
			dest: './geshop/stylesheets'
		},
		javascript: {
			src: './geshop/dev-javascripts/*.js',
			dest: './geshop/javascripts'
		}
	},
	sites: {
		stylesheet: {
			src: './sites/dev-stylesheets/*.less',
			dest: './sites/stylesheets'
		},
		javascript: {
			src: './sites/dev-javascripts/*.js',
			dest: './sites/javascripts'
		}
	}
}

const GEShopStylesheet = function () {
	return src(paths.geshop.stylesheet.src)
		.pipe(less({
			path: [ path.join(__dirname, 'less', 'includes') ]
		}))
		.pipe(cleanCSS())
		.pipe(changed(paths.geshop.stylesheet.dest, { extension: '.css' }))
		.pipe(dest(paths.geshop.stylesheet.dest))
}

const GEShopJavascript = function () {
	return src(paths.geshop.javascript.src)
		.pipe(rollup({
			plugins: [babel()]
		}, 'umd'))
		.pipe(uglify())
		.pipe(changed(paths.geshop.javascript.dest, {extension: '.js'}))
		.pipe(dest(paths.geshop.javascript.dest))
}

const sitesStylesheet = function () {
	return src(paths.sites.stylesheet.src)
		.pipe(less({
			path: [ path.join(__dirname, 'less', 'includes') ]
		}))
		.pipe(cleanCSS())
		.pipe(changed(paths.sites.stylesheet.dest, { extension: '.css' }))
		.pipe(dest(paths.sites.stylesheet.dest))
}

const sitesJavascript = function () {
	return src(paths.sites.javascript.src)
		.pipe(uglify())
		.pipe(changed(paths.sites.javascript.dest, {extension: '.js'}))
		.pipe(dest(paths.sites.javascript.dest))
}

const watcher = function () {
	watch(paths.geshop.stylesheet.src, GEShopStylesheet)
	watch(paths.geshop.javascript.src, GEShopJavascript)
	watch(paths.sites.stylesheet.src, sitesStylesheet)
	watch(paths.sites.javascript.src, sitesJavascript)
}

const build = series(parallel(watcher))

task('default', build)
