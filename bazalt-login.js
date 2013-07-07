(function () {
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
define('bazalt-login/controllers/RegisterCtrl', ['bazalt-login/app'], function(module) {

    module.controller('RegisterCtrl', ['$scope', '$location', 'UserResource', 'bazaltLogin', '$q', function($scope, $location, UserResource, bazaltLogin, $q) {
        $scope.form = {};
        $scope.registerUser = function () {
            var user = new UserResource($scope.form);
            user.$register(function(res) {
                $location.path(bazaltLogin.baseUrl() + '/activationSent');
            }, function(res) {
                if (res.status == 400) $scope.register.invalidForm(res.data);
            });
        };
        $scope.checkEmail = function(email) {
            var d = $q.defer();
            UserResource.checkEmail({'email': email}, function(data) {
                d.resolve(data.valid);
            }, function(error) {
                d.reject(error);
            });
            return d.promise;
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
define('bazalt-login/controllers/LoginCtrl', ['bazalt-login/app'], function(module) {

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
define('bazalt-login/controllers/ModalCtrl', ['bazalt-login/app'], function(module) {

    module.controller('ModalCtrl', ['$scope', function($scope) {
        this.setModel = function(data) {
            $scope.$apply( function() {
                $scope.data = data;
            });
        };
        $scope.setModel = this.setModel;
    }]);

});
define('bazalt-login/controllers', [
    'bazalt-login/controllers/RegisterCtrl',
    'bazalt-login/controllers/LoginCtrl',
    'bazalt-login/controllers/ModalCtrl'
], function(angular) {
});
define('bazalt-login/directives/remoteForm', ['bazalt-login/app'], function(module) {

    module.directive('remoteForm', ['$log', function ($log) {
        return {
            'restrict':'A',
            'scope':false,
            'require': 'form',
            'controller': ['$scope', function ($scope) {
                $scope.invalidForm = function() {
                    
                }
            }],

            'link': function (scope, element, attrs, ctrl) {
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
define('bazalt-login/directives/bvCallback', ['bazalt-login/app'], function(module) {

    module.directive('bvCallback', ['$timeout', function ($timeout) {
        return {
            require: 'ngModel',
            scope: {
                callback: '&bvCallback'
            },
            link: function (scope, elem, attrs, ctrl) {
                //when the scope changes, check the email.
                scope.$watch(attrs.ngModel, function(value) {
                    if (angular.isUndefined(value)) {
                        ctrl.$setValidity('bvCallback', true);
                        return;
                    }
                    // if there was a previous attempt, stop it.
                    if(scope.timer) clearTimeout(scope.timer);

                    // start a new attempt with a delay to keep it from
                    // getting too "chatty".
                    scope.timer = $timeout(function(){
                        scope.callback({ '$value': value }).then(function(data){
                            //set the validity of the field
                            ctrl.$setValidity('bvCallback', data);
                        });
                    }, 200);
                })
            }
        }
    }]);

});
define('bazalt-login/directives/bvCompare', ['bazalt-login/app'], function(module) {

    module.directive('bvCompare', [function () {
        return {
            require: 'ngModel',
            scope: {
                compareField: '='
            },
            link: function (scope, elem, attrs, ctrl) {
                // add a parser that will process each time the value is
                // parsed into the model when the user updates it.
                ctrl.$parsers.unshift(function(value) {
                    // test and set the validity after update.
                    var valid = value == scope.compareField;
                    ctrl.$setValidity('bvCompare', valid);

                    // if it's valid, return the value to the model,
                    // otherwise return undefined.
                    return valid ? value : undefined;
                });

                // add a formatter that will process each time the value
                // is updated on the DOM element.
                ctrl.$formatters.unshift(function(value) {
                    // validate.
                    ctrl.$setValidity('bvCompare', value == scope.compareField);

                    // return the value or nothing will be written to the DOM.
                    return value;
                });
            }
        }
    }]);

});
define('bazalt-login/directives', [
    'bazalt-login/directives/remoteForm',
    'bazalt-login/directives/bvCallback',
    'bazalt-login/directives/bvCompare'
], function(angular) {
});
define('bazalt-login/factories/UserResource', ['bazalt-login/app'], function(module) {

    module.factory('UserResource', ['$resource', '$q', function ($resource, $q) {
        return $resource('/rest.php/user', {}, {
            login: { method: 'POST' },
            checkEmail: { method: 'GET', params: { 'action': 'checkEmail' } },
            register: { method: 'PUT' }
        });
    }]);

});
define('bazalt-login/factories/errorHttpInterceptor', ['bazalt-login/app'], function(module) {

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
define('bazalt-login/factories', [
    'bazalt-login/factories/UserResource',
    'bazalt-login/factories/errorHttpInterceptor'
], function(angular) {
});
define('bazalt-login', ['angular', 'bazalt-login/controllers', 'bazalt-login/directives', 'bazalt-login/factories', 'bazalt-login/app'], function (angular) {
    'use strict';

    return angular.module('bazalt-login');
});}());