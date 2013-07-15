(function () {
define('bazalt-auth/app', ['jquery', 'angular', 'angular-resource', 'angular-cookies'], function ($, angular) {
    'use strict';

    return angular.module('bazalt-auth', ['ngCookies']);
});
define('bazalt-auth/controllers/RegisterCtrl', ['bazalt-auth/app'], function(module) {

    module.controller('RegisterCtrl', ['$scope', '$location', 'UserResource', 'blConfig', '$q', function($scope, $location, UserResource, blConfig, $q) {
        $scope.user = {};
        $scope.registerUser = function () {
            var user = new UserResource($scope.user);
            $scope.loading = true;
            user.$register(function(res) {
                $scope.loading = false;
                $location.path(blConfig.baseUrl() + '/activationSent');
            }, function(res) {
                if (res.status == 400) $scope.register.invalidForm(res.data);
            });
        };
        $scope.checkEmail = function(email) {
            var d = $q.defer();
            UserResource.checkEmail({ 'email': email }, function(data) {
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
define('bazalt-auth/controllers/LoginCtrl', ['bazalt-auth/app'], function(module) {

    module.controller('LoginCtrl', ['$scope', '$location', 'blAcl',
                            function($scope,   $location,   blAcl) {
        $scope.form = {};
        if (blAcl.isLoggedIn()) {
            $location.path('/user/profile');
        }

       $scope.loginUser = function () {
            var data = $scope.form;
            blAcl.login(data, function(user) {
                $location.path('/user/profile');
            }, function(res) {
                if (res.status == 400) $scope.login.invalidForm(res.data);
            });
        };
    }]);

});
define('bazalt-auth/controllers/LogoutCtrl', ['bazalt-auth/app'], function(module) {

    module.controller('LogoutCtrl', ['$scope', '$location', 'blAcl',
                            function($scope,   $location,   blAcl) {
        blAcl.logout(function() {
            $location.path('/');
        });
    }]);

});
define('bazalt-auth/controllers/ModalCtrl', ['bazalt-auth/app'], function(module) {

    module.controller('ModalCtrl', ['$scope', function($scope) {
        this.setModel = function(data) {
            $scope.$apply( function() {
                $scope.data = data;
            });
        };
        $scope.setModel = this.setModel;
    }]);

});
define('bazalt-auth/controllers', [
    'bazalt-auth/controllers/RegisterCtrl',
    'bazalt-auth/controllers/LoginCtrl',
    'bazalt-auth/controllers/LogoutCtrl',
    'bazalt-auth/controllers/ModalCtrl'
], function(angular) {
});
define('bazalt-auth/directives/ngUnique', ['bazalt-auth/app'], function(module) {

    module.directive('ngUnique', ['$parse', function ($parse) {
        return {
            require: 'ngModel',
            scope: false,
            link: function (scope, elem, attrs, ctrl) {
                var callback = $parse(attrs.ngUnique);
                elem.on('blur', function (evt) {
                    scope.$apply(function () {
                        callback(scope, {'$value': elem.val()}).then(function(data){
                            ctrl.$setValidity('unique', data);
                            if (!data) {
                                ctrl.$setViewValue(undefined);
                            }
                        });
                    });
                });
            }
        }
    }
    ]);

});
define('bazalt-auth/directives/bvCompare', ['bazalt-auth/app'], function(module) {

    module.directive('bvCompare', [function () {
        return {
            require: 'ngModel',
            scope: {
                compareField: '=bvCompare'
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
define('bazalt-auth/directives/blAccessLevel', ['bazalt-auth/app'], function(module) {

    module.directive('blAccessLevel', ['blAcl', '$rootScope', function(blAcl, $rootScope) {
        return {
            restrict: 'A',
            scope: {
                'accessLevel': '=blAccessLevel'
            },
            link: function($scope, element, attrs) {
                $scope.user = blAcl.user();
                $rootScope.$watch('user', function(user) {
                    updateCSS();
                }, true);
                $scope.$watch('accessLevel', function(al) {
                    updateCSS();
                }, true);

                function updateCSS() {
                    if ($scope.user && $scope.accessLevel) {
                        $(element).toggle(blAcl.authorize($scope.accessLevel, blAcl.user().role) >= 1);
                    }
                }
            }
        };
    }]);

});
define('bazalt-auth/directives/remoteForm', ['bazalt-auth/app'], function(module) {

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
define('bazalt-auth/directives', [
    'bazalt-auth/directives/ngUnique',
    'bazalt-auth/directives/bvCompare',

    'bazalt-auth/directives/blAccessLevel',

    'bazalt-auth/directives/remoteForm'
], function(angular) {
});
define('bazalt-auth/factories/UserResource', ['bazalt-auth/app'], function(module) {

    module.factory('UserResource', ['$resource', '$q', 'blConfig', function ($resource, $q, blConfig) {
        return $resource(blConfig.apiEndpoint(), {}, {
            login: { method: 'POST' },
            logout: { method: 'DELETE' },
            checkEmail: { method: 'GET', params: { 'action': 'checkEmail' } },
            register: { method: 'PUT' }
        });
    }]);

});
define('bazalt-auth/factories/errorHttpInterceptor', ['bazalt-auth/app'], function(module) {

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
define('bazalt-auth/factories', [
    'bazalt-auth/factories/UserResource',
    'bazalt-auth/factories/errorHttpInterceptor'
], function(angular) {
});
define('bazalt-auth/blConfig', ['bazalt-auth/app'], function (module) {
    'use strict';

    module.provider('blConfig', [function() {
        this.$baseUrl = '/user';

        this.$templateUrl = '/views/user';

        this.$apiEndpoint = '/rest.php/user';

        this.baseUrl = function (baseUrl) {
            this.$baseUrl = baseUrl;
            return this;
        };

        this.templateUrl = function (templateUrl) {
            this.$templateUrl = templateUrl;
            return this;
        };

        this.apiEndpoint = function (apiEndpoint) {
            this.$apiEndpoint = apiEndpoint;
            return this;
        };

        this.$get = function() {
            var self = this;
            return {
                baseUrl: function () {
                    return self.$baseUrl;
                },
                templateUrl: function () {
                    return self.$templateUrl;
                },
                apiEndpoint: function () {
                    return self.$apiEndpoint;
                },
                levels: function() {
                    return self.$levels;
                },
                roles: function() {
                    return self.$roles;
                }
            }
        };

        /*
         Method to build a distinct bit mask for each role
         It starts off with "1" and shifts the bit to the left for each element in the
         roles array parameter
         */
        this.$buildRoles = function (roles) {
            var bitMask = "01", userRoles = {};

            for (var role in roles) {
                var intCode = parseInt(bitMask, 2);
                userRoles[roles[role]] = {
                    bitMask: intCode,
                    title: roles[role]
                };
                bitMask = (intCode << 1 ).toString(2)
            }
            return userRoles;
        }

        /*
         This method builds access level bit masks based on the accessLevelDeclaration parameter which must
         contain an array for each access level containing the allowed user roles.
         */
        this.$buildAccessLevels = function (accessLevelDeclarations, userRoles) {
            var accessLevels = {};
            for (var level in accessLevelDeclarations) {
                if (typeof accessLevelDeclarations[level] == 'string') {
                    if (accessLevelDeclarations[level] == '*') {
                        var resultBitMask = '';

                        for (var role in userRoles) {
                            resultBitMask += "1"
                        }
                        //accessLevels[level] = parseInt(resultBitMask, 2);
                        accessLevels[level] = {
                            bitMask: parseInt(resultBitMask, 2),
                            title: accessLevelDeclarations[level]
                        };
                    } else {
                        console.log("Access Control Error: Could not parse '" + accessLevelDeclarations[level] + "' as access definition for level '" + level + "'")
                    }
                } else {
                    var resultBitMask = 0;
                    for (var role in accessLevelDeclarations[level]) {
                        if (userRoles.hasOwnProperty(accessLevelDeclarations[level][role])) {
                            resultBitMask = resultBitMask | userRoles[accessLevelDeclarations[level][role]].bitMask
                        } else {
                            console.log("Access Control Error: Could not find role '" + accessLevelDeclarations[level][role] + "' in registered roles while building access for '" + level + "'")
                        }
                    }
                    accessLevels[level] = {
                        bitMask: resultBitMask,
                        title: accessLevelDeclarations[level][role]
                    };
                }
            }
            return accessLevels;
        }

        this.$roles = this.$buildRoles([
            'public',
            'user',
            'admin'
        ]);

        this.$levels = this.$buildAccessLevels({
            'public': "*",
            'anon': ['public'],
            'user': ['user', 'admin'],
            'admin': ['admin']
        }, this.$roles);
    }])
    .run(['$rootScope', '$location', 'blConfig', 'blAcl',
  function($rootScope, $location, blConfig, blAcl) {
        $rootScope.$on("$routeChangeStart", function (event, next, current) {
            $rootScope.error = null;

            $rootScope.user = blAcl.user();
            $rootScope.userRoles = blConfig.roles();
            $rootScope.acl = blConfig.levels();

            if (angular.isDefined(next) && angular.isDefined(next.$$route.access) && !blAcl.authorize(next.$$route.access)) {
                if (blAcl.isLoggedIn())
                    $location.path('/');
                else
                    $location.path(blConfig.baseUrl() + '/login');
            }
        });
    }]);

});
define('bazalt-auth/blAcl', ['bazalt-auth/app'], function (module) {
    'use strict';

    module.factory('blAcl', ['$rootScope', 'UserResource', 'blConfig', '$cookieStore', '$log',
                     function($rootScope,   UserResource,   blConfig,   $cookieStore,   $log) {
        var $user = {
            role: blConfig.roles().public
        },
        changeUser = function(user) {
            if (user.login) {
                user.role = blConfig.roles().user;
            } else {
                user.role = blConfig.roles().public;
            }
            $user = user;
            $log.info('User login', $user);
            if (!$rootScope.$$phase) {
                $rootScope.$apply();
            }
        };
        $rootScope.user = $user;
        if ($cookieStore.get('user')) {
            UserResource.get(function(user) {
                if (user) {
                    changeUser(user);
                }
            });
        }

        return {
            authorize: function(accessLevel, role) {
                if (angular.isUndefined(accessLevel)) {
                    return true;
                }
                if(role === undefined)
                    role = $user.role;

                return accessLevel.bitMask & role.bitMask;
            },
            isLoggedIn: function(user) {
                if(user === undefined)
                    user = $user;
                return user.login != undefined && user.login != null;
            },
            register: function(user, success, error) {
                $http.post('/register', user).success(function(res) {
                    changeUser(res);
                    success();
                }).error(error);
            },
            login: function(user, success, error) {
                success = success || angular.noop;
                error = error || angular.noop;
                UserResource.login(user, function(user) {
                    changeUser(user);
                    $cookieStore.put('user', user);
                    success(user);
                }, function(res) {
                    error(res);
                });
            },
            logout: function(success, error) {
                success = success || angular.noop;
                error = error || angular.noop;
                UserResource.logout(function(user){
                    changeUser(user || {});
                    success(user);
                }, error);
            },
            user: function() {
                return $user;
            }
        };
    }]);

});
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
            })
            .when(baseUrl + '/logout', {
                template: 'Loading...',
                controller: 'LogoutCtrl'
            });
    }]);

});
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
});}());