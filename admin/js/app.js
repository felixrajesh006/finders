'use strict';
var app = angular.module("App", [
    'ui.router',
    'ngRoute'
])
.run(function($rootScope) {
    $rootScope.api_root_url = 'http://localhost/finders/core/public';
});

app.config(['$qProvider', function ($qProvider) {
    $qProvider.errorOnUnhandledRejections(false);
}]);
     
        
     
app.config(function($routeProvider) {
    $routeProvider
    .when("/", {
        templateUrl : "views/app/dashboard/main.html"
    })
    .when("/maincategory", {
        templateUrl : "views/app/dashboard/maincategory.html",
        controller : "maincategoryCtrl"
    })
    .when("/green", {
        templateUrl : "views/app/dashboard/dashboard.html",
        controller : "parisCtrl"
    })
    .when("/blue", {
        templateUrl : "blue.htm"
    });
});

 app.directive('demoFileModel', function ($parse) {
        return {
            restrict: 'A', //the directive can be used as an attribute only

            /*
             link is a function that defines functionality of directive
             scope: scope associated with the element
             element: element on which this directive used
             attrs: key value pair of element attributes
             */
            link: function (scope, element, attrs) {
                var model = $parse(attrs.demoFileModel),
                    modelSetter = model.assign; //define a setter for demoFileModel

                //Bind change event on the element
                element.bind('change', function () {
                    //Call apply on scope, it checks for value changes and reflect them on UI
                    scope.$apply(function () {
                        //set the model value
                        modelSetter(scope, element[0].files[0]);
                    });
                });
            }
        };
    });
     