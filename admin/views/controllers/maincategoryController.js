
app.factory("services", ['$http', '$rootScope', function ($http, $rootScope) {
//  var serviceBase = 'services/';
        var serviceBase = $rootScope.api_root_url;
        var obj = {};

        obj.commonService = function (url, data) {
            return $http.post(serviceBase + url, data).then(function (result) {
                return result.data;
            });
        };

        obj.getCustomers = function () {
            return $http.get(serviceBase + 'customers');
        }
        obj.getCustomer = function (customerID) {
            return $http.get(serviceBase + 'customer?id=' + customerID);
        }

        obj.insertCustomer = function (customer) {
            return $http.post(serviceBase + 'insertCustomer', customer).then(function (results) {
                return results;
            });
        };

        obj.updateCustomer = function (id, customer) {
            return $http.post(serviceBase + 'updateCustomer', {id: id, customer: customer}).then(function (status) {
                return status.data;
            });
        };

        obj.deleteCustomer = function (id) {
            return $http.delete(serviceBase + 'deleteCustomer?id=' + id).then(function (status) {
                return status.data;
            });
        };

        return obj;
    }]);

app.controller("maincategoryCtrl", function ($scope, services, $rootScope, $http) {

    $scope.formdata = {};
    $scope.categorylist = {};
    var result = {};

    $scope.addcatlist = function (action) {
        $scope.formdata.action = action;
        services.commonService('/product/getcategorymaster', $scope.formdata);
        $scope.formdata = {};
        $scope.getcatlist('list', '');
    }
    
     $scope.statusupdate = function (status,id) {
        $scope.formdata.action = 'statusupdate';
        $scope.formdata.status = status;
        $scope.formdata.id = id;
        
        var data = $scope.formdata
        var apiurl = $rootScope.api_root_url + '/product/getcategorymaster';
        $http({
            method: "POST",
            url: apiurl,
            data: data
        }).then(function mySuccess(response) {
            $scope.formdata = {};
            $scope.getcatlist('list', '');
        });
    }

    $scope.getcatlist = function (action, id) {

        if (id > 0) {
            $scope.formdata.id = id;
        }

        $scope.formdata.action = action;
        var data = $scope.formdata
        $scope.formdata.action = 'list';
        var apiurl = $rootScope.api_root_url + '/product/getcategorymaster';
        $http({
            method: "POST",
            url: apiurl,
            data: data
        }).then(function mySuccess(response) {
            if (id > 0) {
                $scope.formdata = response.data.response.categorydetails;
            } else {
                $scope.categorylist = response.data.response.categorydetails;
            }

        });


    }


    $scope.getcatlist('list', '');

});