// webpack.config.js
const Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')

    .addEntry('new', './assets/new.js')
    .addEntry('index', './assets/index.js')
    .addEntry('list', './assets/list.js')

    .enableSingleRuntimeChunk()
    .enableSourceMaps(!Encore.isProduction())

    .configureWatchOptions(watchOptions => {
        watchOptions.poll = 1000;
        watchOptions.aggregateTimeout = 300;
    })
;

module.exports = Encore.getWebpackConfig(); 
