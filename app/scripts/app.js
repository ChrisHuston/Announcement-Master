'use strict';

/**
 * @ngdoc overview
 * @name announcementMasterApp
 * @description
 * # announcementMasterApp
 *
 * Main module of the application.
 */
angular
  .module('announcementMasterApp', [
    'ngAnimate',
    'ngRoute',
    'ngSanitize',
    'ngTouch', 'ui.grid', 'multi-select', 'flow'
  ])
  .config(function ($compileProvider, $routeProvider) {
        $compileProvider.debugInfoEnabled(false);
    $routeProvider
      .when('/', {
        templateUrl: 'views/main.html',
        controller: 'MainCtrl'
      })
      .when('/admin', {
        templateUrl: 'views/admin.html?v=1',
        controller: 'AdminCtrl'
      })
      .when('/users', {
        templateUrl: 'views/users.html',
        controller: 'UsersCtrl'
      })
      .otherwise({
        redirectTo: '/'
      });
  });
