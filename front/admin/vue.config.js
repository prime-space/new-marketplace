const path = require("path");
const fs = require('fs');
const assetUrl = '/inc/admin';
let publicPath = '';
let devServer = {};
if (process.env.NODE_ENV === 'production') {
    const config = JSON.parse(fs.readFileSync('../../.env.local.json', 'utf8'));
    publicPath = config.CDN+'/front/'+config.APP_VERSION+'/admin';
} else {
    const devPath = 'https://localhost:8080';
    publicPath = devPath + assetUrl;
    devServer = {
        host: '0.0.0.0',
        port: 8080,
        public: 'localhost:8080',
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
        }
    },
    chainWebpack: config => {
        if (config.plugins.has('extract-css')) {
            const extractCSSPlugin = config.plugin('extract-css')
            extractCSSPlugin && extractCSSPlugin.tap(() => [{
                filename: '[name].css',
                chunkFilename: '[name].css'
            }])
        }
    }
}
