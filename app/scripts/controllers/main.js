'use strict';

/**
 * @ngdoc function
 * @name announcementMasterApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the announcementMasterApp
 */
angular.module('announcementMasterApp')
  .controller('MainCtrl', function ($scope, UserService) {
        $scope.user = UserService.user;

        $scope.toggleShowAnnouncement = function(a) {
            a.show_announcement = !a.show_announcement;
        };

  });
