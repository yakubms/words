const mix = require('laravel-mix');

mix.webpackConfig({
    devServer: {
        host: '0.0.0.0',
        port: 8080
    },
    watchOptions: {
        poll: 2000,
        ignored: /node_modules/
    }
});

mix.js('resources/js/app.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css');

if (mix.inProduction()) {
    mix.version();

    options.terser = {
        terserOptions: {
            compress: {
                drop_console: true
            }
        }
    };
    mix.options(options);
}
