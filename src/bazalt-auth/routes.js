define('bazalt-auth/routes', ['bazalt-auth/app'], function (module) {
    'use strict';

    module.config(['$routeProvider', '$locationProvider', 'baConfigProvider', function($routeProvider, $locationProvider, baConfigProvider) {
        var baseUrl     = baConfigProvider.$baseUrl;

        $routeProvider
            // registration routes
            .when(baseUrl + '/register', {
                templateUrl: function(){ return baConfigProvider.$templateUrl + '/account/registerForm.html'; },
                controller: 'baRegisterCtrl'
            })
            .when(baseUrl + '/terms', {
                templateUrl: function(){ return baConfigProvider.$templateUrl + '/modals/terms.html'; },
                controller: 'baModalCtrl'
            })
            .when(baseUrl + '/privacy', {
                templateUrl: function(){ return baConfigProvider.$templateUrl + '/modals/privacy.html'; },
                controller: 'baModalCtrl'
            })
            .when(baseUrl + '/activationSent', {
                templateUrl: function(){ return baConfigProvider.$templateUrl + '/account/registerSuccessMessage.html'; }
            })
            .when(baseUrl + '/resendActivation', {
                templateUrl: function(){ return baConfigProvider.$templateUrl + '/account/resendActivationForm.html'; },
                controller: 'baRegisterCtrl'
            })
            .when(baseUrl + '/activationResent', {
                templateUrl: function(){ return baConfigProvider.$templateUrl + '/account/activationResentMessage.html'; },
                controller: 'baRegisterCtrl'
            })
            .when(baseUrl + '/activate/:activationKey', {
                templateUrl: function(){ return baConfigProvider.$templateUrl + '/account/activationResentMessage.html'; },
                controller: 'baRegisterCtrl'
            })

            // login routes
            .when(baseUrl + '/login', {
                templateUrl: function(){ return baConfigProvider.$templateUrl + '/account/loginForm.html'; },
                controller: 'baLoginCtrl'
            })
            .when(baseUrl + '/logout', {
                template: 'Loading...',
                controller: 'baLogoutCtrl'
            });
    }]);

});