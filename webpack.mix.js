const mix = require('laravel-mix');
var fs = require('fs');
const fs_extra = require('fs-extra');



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

let publish_folder;
let output_folder;
let source_vue_folder = __dirname+'/Vue';;
let source_assets_folder = "Resources/assets/";


if (mix.inProduction()) {
    /*
     |--------------------------------------------------------------------------
     | Only in Production
     |--------------------------------------------------------------------------
     */
    console.log('---------------------');
    console.log('IN PRODUCTION MODE');
    console.log('---------------------');

    publish_folder = './Resources/assets/';
    output_folder = "./";

    mix.setPublicPath(publish_folder);
    mix.js(source_vue_folder+"/app.js",  output_folder+'/build/app.js').vue();

} else {

    publish_folder = './../../../public/vaahcms/modules/';
    mix.setPublicPath(publish_folder);

    output_folder = "./store/assets/";

    //mix.sass(source_assets_folder+'/scss/build.scss', output_folder+'css/');

    mix.js(source_vue_folder+"/app.js",  output_folder+'/build/app.js').vue();

}

mix.options({
    hmrOptions: {
        host: 'localhost',
        port: 9060,
    },
});

mix.webpackConfig({
    watchOptions: {

        aggregateTimeout: 2000,
        poll: 1000,
        ignored: [
            '**/*.php',
            '**/node_modules',
            '/Config/',
            '/Database/',
            '/Helpers/',
            '/Http/',
            '/jobs/',
            '/Libraries/',
            '/Mails/',
            '/Models/',
            '/node_modules/',
            '/Notifications/',
            '/Providers/',
            '/Resources/',
            '/Routes/',
            '/Tests/',
            '/Wiki/',
        ]
    }
});
