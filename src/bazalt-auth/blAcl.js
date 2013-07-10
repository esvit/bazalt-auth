define('bazalt-auth/blAcl', ['bazalt-auth/app'], function (module) {
    'use strict';

    module.factory('blAcl', ['$rootScope', 'UserResource', 'blConfig', '$cookieStore', function($rootScope, UserResource, blConfig, $cookieStore) {
        var $user = {
            role: blConfig.roles().public
        },
        changeUser = function(user) {
            user.role = blConfig.roles().user
            $user = user;
        };

        $rootScope.user = $user;
        if ($cookieStore.get('user')) {
            UserResource.get(function(user) {
                $rootScope.user = user;
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
                return user.id != undefined;
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
                $http.post('/logout').success(function(){
                    changeUser({
                        username: '',
                        role: userRoles.public
                    });
                    success();
                }).error(error);
            },
            user: function() {
                return $user;
            }
        };
    }]);

});