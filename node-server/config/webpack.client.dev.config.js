const path = require('path');
const merge = require('webpack-merge');
const base = require('./webpack.base.config');
const VueSSRClientPlugin = require('../webpack_modules/vue-server-renderer@2.6.10/client-plugin'); // 本地改造过的插件
const CleanWebpackPlugin = require('clean-webpack-plugin');
const globEntry = require('webpack-glob-entry'); // 多入口插件

module.exports = merge(base, {
    entry: globEntry('./src/entrys/*.js'),
    output: {
        path: path.resolve(__dirname, '../../htdocs/develop/'),
        filename: '[name].bundle.js',
        chunkFilename: 'asyncChunk.[name].js',
        publicPath: "/develop/"
    },
    plugins: [
        // new CleanWebpackPlugin(),
        new VueSSRClientPlugin(),
    ],
    watch: true,
    watchOptions: {
        aggregateTimeout: 300,
        poll: 1000
    },
    optimization: {
		splitChunks: {
            // minSize: 0,
            // minChunks: 1,
            // maxAsyncRequests: 5,
            // maxInitialRequests: 3,
            // name: true,
            cacheGroups: {
				default: {
                    chunks: "async",
					minChunks: 1,
					name: 'all',
				}
			}
		},
	},
});
