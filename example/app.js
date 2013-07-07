// Require.js allows us to configure shortcut alias
require.config({
    shim: {
        angular: {
            exports: 'angular'
        },
        'angular-resource': {
            exports: 'angular'
        },
        'views': {
            deps: ['main']
        }
    },
    paths: {
        jquery: '../components/jquery/jquery.min',
        angular: '../components/angular/angular.min',
        'angular-resource': '../components/angular-resource/angular-resource.min',
        'angular-loader': '../components/angular-loader/angular-loader.min',
        'elasticjs': '../components/elastic.js/dist/elastic.min',
        'angular-elastic': '../components/elastic.js/dist/elastic-angular-client.min',
        'social-likes': '../components/social-likes/social-likes.min',
        'jquery-timeago': '../components/jquery-timeago/jquery.timeago',
        'ng-table': '../components/ng-table/ng-table'
    }
});

define('app', ['jquery', 'angular', '.', 'main', 'controllers', 'factories', 'directives'], function ($, angular) {
    'use strict';

    angular.bootstrap(document.documentElement, ['app']);
});

require(['app'], function() {});