const path = require('path');
const merge = require('webpack-merge');
const nodeExternals = require('webpack-node-externals');
const VueSSRServerPlugin = require('vue-server-renderer/server-plugin');
const base = require('./webpack.base.config');

module.exports = merge(base, {
    target: 'node',
    // 对 bundle renderer 提供 source map 支持
    devtool: '#source-map',
    entry: {
        server: path.resolve(__dirname, '../src/entry-server.js')
    },
    externals: [nodeExternals()],
    output: {
        libraryTarget: 'commonjs2'
    },
    plugins: [
        new VueSSRServerPlugin(), // 这个要放到第一个写，否则 CopyWebpackPlugin 不起作用，原因还没查清楚
    ],
    watch: true,
    watchOptions: {
        aggregateTimeout: 300,
        poll: 1000
    },
});
