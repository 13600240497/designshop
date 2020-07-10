const path = require('path')
const merge = require('webpack-merge')
const base = require('./webpack.base.config')
const VueSSRClientPlugin = require('../webpack_modules/vue-server-renderer@2.6.10/client-plugin'); // 本地改造过的插件
const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;
const CleanWebpackPlugin = require('clean-webpack-plugin');
const S3Plugin = require('webpack-s3-plugin')
const globEntry = require('webpack-glob-entry'); // 多入口插件


module.exports = merge(base, {
    mode: 'production',
    entry: globEntry('./src/entrys/*.js'),
    output: {
        path: path.resolve(__dirname, '../../htdocs/resource/'),
        filename: '[name].bundle.[hash:8].js',
        chunkFilename: '[name].[hash:8].js',
        publicPath: "//geshopcss.logsss.com/vueComponent/"
    },
    // optimization: {
    //     runtimeChunk: 'single',
    //     splitChunks: {
    //         chunks: 'all',
    //         maxInitialRequests: Infinity,
    //         minSize: 20000,
    //         cacheGroups: {
    //             vendor: {
    //                 test: /node_modules/,
    //                 name (module) {
    //                     // get the name. E.g. node_modules/packageName/not/this/part.js
    //                     // or node_modules/packageName
    //                     const packageName = module.context.match(/[\\/]node_modules[\\/](.*?)([\\/]|$)/)[1]
    //                     // npm package names are URL-safe, but some servers don't like @ symbols
    //                     return `npm.${packageName.replace('@', '')}`
    //                 }
    //             }
    //         }
    //     }
    // },

    plugins: [
        new CleanWebpackPlugin(),
        new VueSSRClientPlugin(),
        new S3Plugin({
            // Exclude uploading of html
            include: /.*\.(jpg|png|js)$/,
            // s3Options are required
            s3Options: {
                accessKeyId: 'AKIAJ6BHMQEYKUWWXGEQ',
                secretAccessKey: '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',
                region: 'us-east-1'
            },
            s3UploadOptions: {
                Bucket: 'css.appinthestore.com',
                ACL: 'private',
            },
            basePathTransform: function() {
                return '/vueComponent/'
            }
        }),
        // new BundleAnalyzerPlugin({
        //     openAnalyzer: true
        // })
    ],
});
