const path = require('path');
const VueLoaderPlugin = require('vue-loader/lib/plugin');

module.exports = {
    mode: 'development',

    resolve: {
        extensions: ['.js', '.vue'],
        alias: {
            '@lib': path.resolve(__dirname, '../src/lib'),
            '@htdocs': path.resolve(__dirname, '../../htdocs')
        }
    },

    output: {
        path: path.resolve(__dirname, '../dist'),
        filename: '[name].bundle.js',
        jsonpFunction: 'geshopUIJsonp'
    },

    module: {
        rules: [
            {
                test: /\.(js|vue)$/,
                // exclude: /(node_modules|parts)/,
                exclude: /(node_modules|U000182|U000235|U000209)/,
                loader: 'eslint-loader',
                enforce: 'pre',
                options: { // eslint options (if necessary)
                }
            },
            {
                test: /\.vue$/,
                use: 'vue-loader'
            },
            {
                test: /\.js$/,
                exclude: /(node_modules)/,
                use: 'babel-loader'
            },
            {
                test: /\.css$/,
                use: ['vue-style-loader', 'css-loader']
            },
            {
                test: /\.less$/,
                use: ['vue-style-loader', 'css-loader', 'less-loader']
            },
            {
                test: /\.(jpg|jpeg|png|gif|svg)$/,
                use: {
                    loader: 'url-loader',
                    options: {
                        limit: 10000 // 10Kb
                    }
                }
            }
        ]
    },
    plugins: [
        new VueLoaderPlugin()
    ]
};
