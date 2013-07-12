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