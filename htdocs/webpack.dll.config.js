const path = require('path');
const webpack = require('webpack');
const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin // 模块包分析

module.exports = {
    mode: 'production',
    entry: {
        vendor: [ // 提前打包一些基本不怎么修改的文件
            'element-ui',
            'vue',
            'ant-design-vue'
        ]
    },
    output: {
        filename: 'resource_dll/[name].dll.js',
        path: path.resolve(__dirname, './resource_dll'),
        library: '_dll_vendor' // 暴露出的全局变量名
        // 打包出来的manifest.json和dll.js的n//可选 暴露出的全局变量名
        // vendor.dll.js中暴露出的全局变量名，主要是给DllPlugin中的name使用，
        // 故这里需要和webpack.DllPlugin中的`name: '_dll_vendor',`保持一致。
    },
    plugins: [
        new webpack.DllPlugin({
            path: path.join(__dirname, './resource_dll/vendor-manifest.json'),
            name: '_dll_vendor' // 公开的dll函数的名称，和output. library保持一致即可
        }),
		new BundleAnalyzerPlugin(),
    ]
};