const mix = require('laravel-mix');
// const tailwindcss = require('tailwindcss');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

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
// .options({
//     postCss: [
//         tailwindcss('tailwind.config.js'),
//     ],
//     processCssUrls: false
// });

if (mix.inProduction()) {
    mix.version();
}
