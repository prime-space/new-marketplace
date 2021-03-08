const path = require("path");
const fs = require('fs');
const assetUrl = '/inc/customer';
let publicPath = '';
let devServer = {};
if (process.env.NODE_ENV === 'production') {
    const config = JSON.parse(fs.readFileSync('../../.env.local.json', 'utf8'));
    publicPath = config.CDN+'/front/'+config.APP_VERSION+'/customer';
} else {
    const devPath = 'https://localhost:8086';
    publicPath = devPath + assetUrl;
    devServer = {
        host: '0.0.0.0',
        port: 8086,
        public: 'localhost:8086',
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
            chunkFilename: '[name].js',
        },
        plugins: [
            // new VuetifyLoaderPlugin(),
        ],
    },
    chainWebpack: config => {
        const inlineLimit = 10000;

        config
            .plugin('provide')
            .use(require('webpack').ProvidePlugin, [{
                Main: path.resolve(path.join(__dirname, 'Main.js')),
            }]);

        config.module
            .rule('images')
            .test(/\.(png|jpe?g|gif)(\?.*)?$/)
            .use('url-loader')
            .loader('url-loader')
            .options({
                limit: inlineLimit,
                name: path.join('img/[name].[hash:8].[ext]'),
            });

        config.module
            .rule('var2')
            .test(/\.scss$/)
            .use('vue-style-loader')
            .loader('sass-loader')
            .options({
                prependData: "@import './src/scss/vuetify.scss';",
            });

        config.module
            .rule('fonts')
            .test(/\.(woff2?|eot|ttf|otf)(\?.*)?$/i)
            .use('url-loader')
            .loader('url-loader')
            .options({
                limit: inlineLimit,
                name: path.join('fonts/[name].[hash:8].[ext]'),
            });

        if (config.plugins.has('extract-css')) {
            const extractCSSPlugin = config.plugin('extract-css');
            extractCSSPlugin && extractCSSPlugin.tap(() => [{
                filename: '[name].css',
                chunkFilename: '[name].css',
            }])
        }
    }
};
