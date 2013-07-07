define('bazalt-login/app', ['jquery', 'angular', 'angular-resource'], function ($, angular) {
    'use strict';

    return angular.module('bazalt-login', [])
        .provider('bazaltLogin', function () {
            this.$baseUrl = '/user';
            this.$templateUrl = '/views/user';

            this.baseUrl = function(baseUrl) {
                this.$baseUrl = baseUrl;
            }

            this.templateUrl = function(templateUrl) {
                this.$templateUrl = templateUrl;
            }

            this.$get = function() {
                var self = this;
                return {
                    baseUrl: function() {
                        return self.$baseUrl;
                    },
                    templateUrl: function() {
                        return self.$templateUrl;
                    }
                }
            };
        })
        .config(['$routeProvider', '$locationProvider', 'bazaltLoginProvider', function($routeProvider, $locationProvider, bazaltLoginProvider) {
            var baseUrl     = bazaltLoginProvider.$baseUrl,
                templateUrl = bazaltLoginProvider.$templateUrl;
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