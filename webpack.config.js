// webpack.config.js
const Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('Resources/public/') // Сборка без лишней вложенности
    .setPublicPath('/bundles/Slcorpadmin') // Symfony ищет файлы здесь
    .setManifestKeyPrefix('bundles/Slcorpadmin')
    .addEntry('app', './assets/js/app.js')
    .addEntry('table', './assets/js/table.js')
    .addEntry('tree', './assets/js/tree.js')
    .enableStimulusBridge('./assets/controllers.json')
    .enableSingleRuntimeChunk() // Включаем runtime chunk
    .cleanupOutputBeforeBuild() // Очищаем выходную директорию перед сборкой
    .enableSourceMaps(!Encore.isProduction()) // Включаем source maps для разработки
    .enableVersioning(Encore.isProduction())
    .configureManifestPlugin((options) => {
        options.fileName = 'manifest.json'; // Гарантируем стандартное имя
    });

module.exports = Encore.getWebpackConfig();