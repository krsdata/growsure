var app = angular.module('growSure', ['ngRoute']);


app.config(function ($routeProvider) {
    $routeProvider
    .when("/", {
        templateUrl: "app/views/dashboard.html"
    })
    
    .otherwise({
        redirect: '/'
    });;
});

app.config(function ($httpProvider) {
    $httpProvider.defaults.headers.common = {};
    $httpProvider.defaults.headers.post = {};
    $httpProvider.defaults.headers.put = {};
    $httpProvider.defaults.headers.patch = {};
});