require.config({
    baseUrl: '../src',
    shim: {
        'angular': {
            exports: 'angular'
        },
        'angular-resource': {
            deps: ['angular']
        }
    },
    paths: {
        'jquery': '../bower_components/jquery/jquery',
        'angular': '../bower_components/angular/angular',
        'angular-resource': '../bower_components/angular/angular-resource',
        'angular-cookies': '../bower_components/angular/angular-cookies'
    }
});

define(['angular', '../example/main'], function (angular, a) {
    'use strict';

    angular.bootstrap(document.documentElement, ['app']);
});