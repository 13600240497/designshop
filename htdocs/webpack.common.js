const path = require('path')
const globEntry = require('webpack-glob-entry')
const ManifestPlugin = require('webpack-manifest-plugin')

module.exports = {
	// entry: globEntry('./src/main-entry.js'),
	 entry: globEntry('./src/*.js'),
	//entry: globEntry('./src/activityZF.js'),
	output: {
		path: path.resolve(__dirname, 'dist'),
		publicPath: '/dist/'
	},
	plugins: [
		new ManifestPlugin({
			basePath: '',
			publicPath: ''
		})
	],
	resolve: {
		extensions: ['.vue', '.js'],
		alias: {
			'vue$': 'vue/dist/vue.esm.js'
		}
	},
	module: {
		rules: [{
			test: /\.vue$/,
			loader: 'vue-loader'
		}, {
			test: /\.css$/,
			use: ['style-loader', 'css-loader']
		}, {
			test: /\.less$/,
			use: [{
				loader: 'style-loader'
			}, {
				loader: 'css-loader'
			}, {
				loader: 'less-loader'
			}]
		}, {
			test: /\.(woff2?|eot|ttf|otf)(\?.*)?$/,
			use: [{
				loader: 'url-loader',
				options: {
					limit: 10000,
					name: '/fonts/[name].[ext]'
				}
			}]
		}, {
			test: /\.(png|jpe?g|gif|svg)(\?.*)?$/,
			loader: 'url-loader',
			options: {
				limit: 10000
			}
		}]
	}
}
