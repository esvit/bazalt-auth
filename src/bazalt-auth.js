define('bazalt-auth',
    [
        'angular',
        'bazalt-auth/controllers',
        'bazalt-auth/directives',
        'bazalt-auth/factories',
        'bazalt-auth/app',
        'bazalt-auth/baConfig',
        'bazalt-auth/baAcl',
        'bazalt-auth/routes'
    ], function (angular) {
    'use strict';

    return angular.module('bazalt-auth');
});