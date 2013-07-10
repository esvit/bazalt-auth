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