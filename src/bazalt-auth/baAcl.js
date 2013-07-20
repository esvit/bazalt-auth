define('bazalt-auth/baAcl', ['bazalt-auth/app'], function (module) {
    'use strict';

    module.factory('baAcl', ['$rootScope', 'baSessionResource', 'baConfig', '$cookieStore', '$log',
                     function($rootScope,   baSessionResource,   baConfig,   $cookieStore,   $log) {
        var $user = {
            acl: {
                guest: 1
            }
        },
        changeUser = function(user) {
            if (!user.acl) {
                user.acl = {};
            }
            $user = user;
            $log.info('User login', $user);
            $rootScope.$emit('baUserLogin', { 'user': $user });
            if (!$rootScope.$$phase) {
                $rootScope.$apply();
            }
        };
        $rootScope.user = $user;
        if ($cookieStore.get('user')) {
            baSessionResource.get(function(user) {
                if (user) {
                    changeUser(user);
                }
            });
        }

        return {
            hasRight: function(accessLevel, role) {
                var levels = baConfig.levels(),
                    level = accessLevel.split('.'),
                    bit = levels;
                role = role || $user.acl;
                for (var i = 0; i < level.length; i++) {
                    if (!bit[level[i]]) {
                        $log.error('Role "' + accessLevel + '" not found');
                        return false;
                    }
                    bit = bit[level[i]];
                }
                if (!role[level[0]]) {
                    return false;
                }
                return (bit & role[level[0]]) >= 1;
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
                baSessionResource.login(user, function(user) {
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
                baSessionResource.logout(function(user){
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