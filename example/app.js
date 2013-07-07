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
        'angular-resource': '../bower_components/angular-resource/angular-resource'
    }
});

define(['angular', '../example/main'], function (angular, a) {
    'use strict';
console.info(a);
    angular.bootstrap(document.documentElement, ['app']);
});