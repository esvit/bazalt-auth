(function () {
define('bazalt-login', ['jquery', 'angular', 'angular-resource'], function ($, angular) {
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
define('controllers/RegisterCtrl',['bazalt-login'], function(module) {

    module.controller('RegisterCtrl', ['$scope', '$location', 'UserResource', 'bazaltLogin', function($scope, $location, UserResource, bazaltLogin) {
        $scope.form = {};
        $scope.registerUser = function () {
            var user = new UserResource($scope.form);
            user.$register(function(res) {
                $location.path(bazaltLogin.baseUrl() + '/activationSent');
            }, function(res) {
                if (res.status == 400) $scope.register.invalidForm(res.data);
            });
        };
        $scope.resendActivation = function () {
            $http.post('/account/resendActivation', $scope.form)
            .success(function(data) {
                $location.path('/activationResent');
            })
            .error(function(data, status, headers, config) {
                $scope.error = data.error.message;
            });
        };

    }]);

});
define('controllers/LoginCtrl',['bazalt-login'], function(module) {

    module.controller('LoginCtrl', ['$scope', '$location', 'UserResource', function($scope, $location, UserResource) {
        $scope.form = {};
        $scope.loginUser = function () {
            var data = $scope.form;
            UserResource.login(data, function(res) {
                //$location.path('/');
                //console.info(res);
            }, function(res) {
                if (res.status == 400) $scope.login.invalidForm(res.data);
            });
        };
    }]);

});
define('controllers/ModalCtrl',['bazalt-login'], function(module) {

    module.controller('ModalCtrl', ['$scope', function($scope) {
        this.setModel = function(data) {
            $scope.$apply( function() {
                $scope.data = data;
            });
        };
        $scope.setModel = this.setModel;
    }]);

});
define('controllers', [
    'controllers/RegisterCtrl',
    'controllers/LoginCtrl',
    'controllers/ModalCtrl'
], function(angular) {
});
define('directives/remoteForm',['bazalt-login'], function(module) {

    module.directive('remoteForm', ['$log', function ($log) {
        return {
            'restrict':'A',
            'scope':false,
            'require': 'form',
            'controller':function ($scope, $element, $attrs) {
                $scope.invalidForm = function() {
                    
                }
            },

            'link':function (scope, element, attrs, ctrl) {
                ctrl.invalidForm = function(data) {
                    $log.error(data);
                    angular.forEach(data, function(field, fieldName) {
                        var ctr = ctrl[fieldName] || null;
                        if (!ctr) {
                            $log.error('Field not found', fieldName);
                        } 
                        angular.forEach(field, function(error, validator) {
                            if (!ctr) {
                                $log.error(error);
                            } else {
                                ctr.$setValidity(validator, false);
                            }
                        });
                    });
                }
            }
        }
    }])

});
define('directives', [
    'directives/remoteForm'
], function(angular) {
});
define('factories/UserResource',['bazalt-login'], function(module) {

    module.factory('UserResource', ['$resource', '$q', function ($resource, $q) {
        return $resource('/rest.php/user', {}, {
            login: { method: 'POST' },
            register: { method: 'PUT' }
        });
    }]);

});
define('factories/errorHttpInterceptor',['bazalt-login'], function(module) {

    module.factory('errorHttpInterceptor', ['$q', function($q) {
        return function (promise) {
            return promise.then(function (response) {
                return response;
            }, function (response) {
                if (response.status == 500) {
                    console.error(response.data.error.message, response.data.error.file);
                }
                if (response.status == 405) {
                    console.error(response.data);
                }
                return $q.reject(response);
            });
        };
      }]);

});
define('factories', [
    'factories/UserResource',
    'factories/errorHttpInterceptor'
], function(angular) {
});}());