define('bazalt-auth/baConfig', ['bazalt-auth/app'], function (module) {
    'use strict';

    module.provider('baConfig', [function() {
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
    .run(['$rootScope', '$location', 'baConfig', 'baAcl',
  function($rootScope,   $location,   baConfig,   baAcl) {
        var setAcl = function() {
            $rootScope.error = null;

            $rootScope.user = baAcl.user();
            $rootScope.userRoles = baConfig.roles();
            $rootScope.acl = baConfig.levels();
            console.info($rootScope.acl);
        };
        $rootScope.$on("$routeChangeStart", function(event, next, current) {
            setAcl();
            if (angular.isDefined(next) && angular.isDefined(next.$$route) &&
                next.$$route.hasOwnProperty('access') && !baAcl.authorize(next.$$route.access)) {
                if (baAcl.isLoggedIn())
                    $location.path('/');
                else
                    $location.path(baConfig.baseUrl() + '/login');
            }
        });
        setAcl();
    }]);

});