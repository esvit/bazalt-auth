define('bazalt-auth/routes', ['bazalt-auth/app'], function (module) {
    'use strict';

    module.config(['$routeProvider', '$locationProvider', 'baConfigProvider', function($routeProvider, $locationProvider, baConfigProvider) {
        var baseUrl     = baConfigProvider.$baseUrl,
            templateUrl = baConfigProvider.$templateUrl;

        $routeProvider
            // registration routes
            .when(baseUrl + '/register', {
                templateUrl: templateUrl + '/account/registerForm.html',
                controller: 'baRegisterCtrl'
            })
            .when(baseUrl + '/terms', {
                templateUrl: templateUrl + '/modals/terms.html',
                controller: 'baModalCtrl'
            })
            .when(baseUrl + '/privacy', {
                templateUrl: templateUrl + '/modals/privacy.html',
                controller: 'baModalCtrl'
            })
            .when(baseUrl + '/activationSent', {
                templateUrl: templateUrl + '/account/registerSuccessMessage.html'
            })
            .when(baseUrl + '/resendActivation', {
                templateUrl: templateUrl + '/account/resendActivationForm.html',
                controller: 'baRegisterCtrl'
            })
            .when(baseUrl + '/activationResent', {
                templateUrl: templateUrl + '/account/activationResentMessage.html',
                controller: 'baRegisterCtrl'
            })
            .when(baseUrl + '/activate/:activationKey', {
                templateUrl: templateUrl + '/account/activationResentMessage.html',
                controller: 'baRegisterCtrl'
            })

            // login routes
            .when(baseUrl + '/login', {
                templateUrl: templateUrl + '/account/loginForm.html',
                controller: 'baLoginCtrl'
            })
            .when(baseUrl + '/logout', {
                template: 'Loading...',
                controller: 'baLogoutCtrl'
            });
    }]);

});