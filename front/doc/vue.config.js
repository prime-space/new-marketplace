const path = require("path");
const fs = require('fs');
const VuetifyLoaderPlugin = require('vuetify-loader/lib/plugin');
let publicPath = '';
let devServer = {};
const assetUrl = '/inc/doc';
if (process.env.NODE_ENV === 'production') {
    const config = JSON.parse(fs.readFileSync('../../.env.local.json', 'utf8'));
    publicPath = config.CDN+'/front/'+config.APP_VERSION+'/doc';
} else {
    const devPath = 'https://localhost:8086';
    publicPath = devPath + assetUrl;
    devServer = {
        host: '0.0.0.0',
        port: 8085,
        public: 'localhost:8085',
        disableHostCheck: true,
        https: {key: fs.readFileSync('/etc/ssl/127.0.0.1-key.pem'), cert: fs.readFileSync('/etc/ssl/127.0.0.1.pem')},
    };
}
const assetPath = './../../public' + assetUrl;

module.exports = {
    transpileDependencies: ['vuetify'],
    publicPath,
    devServer,
    outputDir: path.resolve(__dirname, assetPath),
    configureWebpack: {
        output: {
            path: path.resolve(__dirname, assetPath),
            filename: '[name].js',
            chunkFilename: '[name].js'
        },
        plugins: [
            new VuetifyLoaderPlugin(),
        ],
    },
    chainWebpack: config => {
        if (config.plugins.has('extract-css')) {
            const extractCSSPlugin = config.plugin('extract-css');
            extractCSSPlugin && extractCSSPlugin.tap(() => [{
                filename: '[name].css',
                chunkFilename: '[name].css'
            }])
        }
    }
}
