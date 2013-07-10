define('bazalt-auth',
    [
        'angular',
        'bazalt-auth/controllers',
        'bazalt-auth/directives',
        'bazalt-auth/factories',
        'bazalt-auth/app',
        'bazalt-auth/blConfig',
        'bazalt-auth/blAcl',
        'bazalt-auth/routes'
    ], function (angular) {
    'use strict';

    return angular.module('bazalt-auth');
});