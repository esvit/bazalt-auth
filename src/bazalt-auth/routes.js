define('bazalt-auth/routes', ['bazalt-auth/app'], function (module) {
    'use strict';

    module.config(['$routeProvider', '$locationProvider', 'blConfigProvider', function($routeProvider, $locationProvider, blConfigProvider) {
        var baseUrl     = blConfigProvider.$baseUrl,
            templateUrl = blConfigProvider.$templateUrl;

        $routeProvider
            // registration routes
            .when(baseUrl + '/register', {
                templateUrl: templateUrl + '/account/registerForm.html',
                controller: 'RegisterCtrl'
            })
            .when(baseUrl + '/terms', {
                templateUrl: templateUrl + '/modals/terms.html',
                controller: 'ModalCtrl'
            })
            .when(baseUrl + '/privacy', {
                templateUrl: templateUrl + '/modals/privacy.html',
                controller: 'ModalCtrl'
            })
            .when(baseUrl + '/activationSent', {
                templateUrl: templateUrl + '/account/registerSuccessMessage.html'
            })
            .when(baseUrl + '/resendActivation', {
                templateUrl: templateUrl + '/account/resendActivationForm.html',
                controller: 'RegisterCtrl'
            })
            .when(baseUrl + '/activationResent', {
                templateUrl: templateUrl + '/account/activationResentMessage.html',
                controller: 'RegisterCtrl'
            })
            .when(baseUrl + '/activate/:activationKey', {
                templateUrl: templateUrl + '/account/activationResentMessage.html',
                controller: 'RegisterCtrl'
            })

            // login routes
            .when(baseUrl + '/login', {
                templateUrl: templateUrl + '/account/loginForm.html',
                controller: 'LoginCtrl'
            });
    }]);

});