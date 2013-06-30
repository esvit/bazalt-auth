define(['main'], function(module) {

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