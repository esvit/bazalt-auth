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