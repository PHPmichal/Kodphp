window.onload = start;
function start() {
    var app = angular.module("myApp", ["ngRoute"]);
    app.config(function($routeProvider) {
        $routeProvider
            .when("/", {
                templateUrl : "src/home.html",
            })
            .when("/dodaj", {
                templateUrl : "src/dodaj.html",
                //controller : "dodaj"
            })
            .when("/statystyki", {
                templateUrl : "src/statystyki.html",
                // controller : "statystyki"
            })
        .when("/ustawienia", {
                templateUrl : "src/ustawienia.html",
                // controller : "statystyki"
            });
    });
}

