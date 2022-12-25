const mix = require('laravel-mix');

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

mix.js('resources/js/company/recruit/detail.js', 'public/js/company/recruit_detail.js')
    .js('resources/js/company/outsource/detail.js', 'public/js/company/outsource_detail.js')
    .js('resources/js/recruit/detail.js', 'public/js/recruit/apply_detail.js')
    .js('resources/js/outsource/detail.js', 'public/js/outsource/apply_detail.js')
    .js('resources/js/jobseeker/detail.js', 'public/js/jobseeker/jobseeker_detail.js')
    .js('resources/js/app.js', 'public/js')
    .vue()
    .sass('resources/sass/app.scss', 'public/css');
