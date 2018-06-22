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



app.controller("parisCtrl", function ($scope) {
    $scope.msg = "I love Paris";
});