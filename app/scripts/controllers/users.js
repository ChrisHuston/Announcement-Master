'use strict';

/**
 * @ngdoc function
 * @name announcementMasterApp.controller:AboutCtrl
 * @description
 * # AboutCtrl
 * Controller of the announcementMasterApp
 */
angular.module('announcementMasterApp')
  .controller('UsersCtrl', function ($scope, UserService, $http, uiGridConstants) {
        $scope.admin = UserService.admin;
        $scope.showGrid = false;
        $scope.currentSection = 1;
        $scope.newCourse = null;

        $scope.changeCourse = function() {
            $scope.newCourse = angular.copy($scope.selectedCourse);
        };

        $scope.addCourse = function() {
            UserService.addCanvasCourse($scope.newCourse);
        };

        $scope.saveCourse = function() {
            UserService.updateCanvasCourse($scope.newCourse);
        };

        $scope.deleteCourse = function() {
            UserService.deleteCanvasCourse($scope.newCourse);
        };

        $scope.editCourseName = function() {
            UserService.updateCourse($scope.admin.course_name);
        };

        $scope.changeSectionNumber = function(s) {
            UserService.changeSectionNumber(s);
        };

        $scope.deleteSection = function(s) {
            UserService.deleteSection(s);
        };

        var getUsers = function() {
            var uniqueSuffix = "?" + new Date().getTime();
            var params = {};
            params.course_id = UserService.admin.course_id;
            $http({method: 'POST',
                url: UserService.appDir + 'php/getUsers.php' + uniqueSuffix,
                data: params,
                headers: {'Content-Type': 'application/x-www-form-urlencoded'}
            }).
                success(function(data) {
                    if (data.users.length>0) {
                        $scope.userGridOptions.data = data.users;
                        $scope.showGrid = true;
                    }
                }).
                error(function(data, status) {
                    alert("Error: " + status + " Get users failed. Check your internet connection");
                })
        };

        getUsers();

        $scope.getCourseUsers = function() {
            var uniqueSuffix = "?" + new Date().getTime();
            var params = {};
            params.canvas_course_id = $scope.newCourse.canvas_course_id;
            $http({method: 'POST',
                url: UserService.appDir + 'php/getCourseUsers.php' + uniqueSuffix,
                data: params,
                headers: {'Content-Type': 'application/x-www-form-urlencoded'}
            }).
                success(function(data) {
                    var users = data.users;
                    var members = "";
                    var ids = "(";
                    var user_index = 1;

                    angular.forEach($scope.admin.sections, function(s) {
                        if (s.canvas_course_id === $scope.newCourse.canvas_course_id) {
                            s.num_members = 0;
                        }
                    });
                    var course_users = [];
                    angular.forEach(users, function(s) {
                        var role_id;
                        console.log(s.type);
                        if (s.type === 'StudentEnrollment') {
                            role_id = 1;
                        } else if(s.type === 'ObserverEnrollment') {
                            role_id = 2;
                        } else if (s.type === 'TaEnrollment') {
                            role_id = 3;
                        } else if(s.type === 'DesignerEnrollment') {
                            role_id = 4;
                        } else if(s.type === 'TeacherEnrollment') {
                            role_id = 5;
                        } else if(s.type === 'StudentViewEnrollment') {
                            role_id = 0;
                        }
                        if (role_id != 0) {
                            s.net_id = s.user.login_id;
                            s.section_id = s.course_section_id;
                            s.user_name = s.user.sortable_name;
                            s.email = s.user.sis_user_id;
                            s.canvas_user_id = s.user_id;
                            for (var i=0; i < $scope.admin.sections.length; i++) {
                                if (parseInt($scope.admin.sections[i].section_id) === parseInt(s.section_id)) {
                                    s.section_num = $scope.admin.sections[i].section_num;
                                    $scope.admin.sections[i].num_members += 1;
                                    break;
                                }
                            }
                            s.role_id = role_id;
                            course_users.push(s);

                            var member = "(" + $scope.admin.course_id + ",'" + s.net_id + "'" + ',"' + s.user_name + '","' + s.email + '",' + s.role_id + "," + s.section_id + "," + s.canvas_user_id + "," + $scope.newCourse.canvas_course_id + ")";
                            ids += "'" + s.net_id + "'";

                            member += ", ";
                            ids += ", ";
                            members += member;
                        }
                        user_index += 1;
                    });
                    members = members.slice(0,-2);
                    ids = ids.slice(0,-2);
                    ids += ")";
                    addUsers(members, ids);
                    console.log(members);
                    console.log(ids);
                    //$scope.userGridOptions.data = _.union($scope.userGridOptions.data, course_users);
                }).
                error(function(data, status) {
                    alert("Error: " + status + " Get users failed. Check your internet connection");
                });
        };

        var addUsers = function(members, ids) {
            var uniqueSuffix = "?" + new Date().getTime();
            var php_script;
            php_script = "addUsers.php";
            var params = {};
            params.members = members;
            params.ids = ids;
            params.canvas_course_id = $scope.newCourse.canvas_course_id;
            $http({method: 'POST',
                url: UserService.appDir + 'php/' + php_script + uniqueSuffix,
                data: params,
                headers: {'Content-Type': 'application/x-www-form-urlencoded'}
            }).
                success(function(data) {
                    if (data) {
                        getUsers();
                    }
                    console.log(data);
                }).
                error(function(data, status) {
                    alert( "Error: " + status + " Add users failed. Check your internet connection");
                });
        };

        var addSections = function(inserts) {
            var uniqueSuffix = "?" + new Date().getTime();
            var php_script;
            php_script = "addSections.php";
            var params = {};
            params.inserts = inserts;
            $http({method: 'POST',
                url: UserService.appDir + 'php/' + php_script + uniqueSuffix,
                data: params,
                headers: {'Content-Type': 'application/x-www-form-urlencoded'}
            }).
                success(function(data) {
                    console.log(data);
                }).
                error(function(data, status) {
                    alert( "Error: " + status + " Add sections failed. Check your internet connection");
                });
        };

        $scope.getSections = function() {
            var uniqueSuffix = "?" + new Date().getTime();
            var php_script;
            php_script = "getSections.php";
            var params = {};
            params.canvas_course_id = $scope.newCourse.canvas_course_id;
            $http({method: 'POST',
                url: UserService.appDir + 'php/' + php_script + uniqueSuffix,
                data: params,
                headers: {'Content-Type': 'application/x-www-form-urlencoded'}
            }).
                success(function(data) {
                    var sections = JSON.parse(data.sections);
                    sections = _.sortBy(sections, function(s) {return s.id;});
                    var section_num = 1;
                    if ($scope.admin.sections.length>0) {
                        section_num = parseInt($scope.admin.sections[$scope.admin.sections.length-1].section_num) + 1;
                    }
                    var inserts = "";
                    angular.forEach(sections, function(s) {
                        var has_section = _.find($scope.admin.sections, function(sect) {
                            return parseInt(sect.section_id) === s.id;
                        });
                        if (has_section === undefined) {
                            var dash_split = s.name.split("-");
                            if (dash_split.length > 1) {
                                section_num = parseInt(dash_split[dash_split.length-1]);
                            } else {
                                section_num += 1;
                            }
                            s.section_num = section_num;
                            s.section_name = 'Section ' + s.section_num;
                            s.canvas_course_id = $scope.newCourse.canvas_course_id;
                            s.section_id = s.id;
                            var values = "(" + $scope.newCourse.canvas_course_id + "," + s.section_num + "," + s.section_id + "), ";
                            inserts += values;
                            $scope.admin.sections.push(s);

                        }
                    });
                    if (inserts !== "") {
                        inserts = inserts.slice(0,-2);
                        addSections(inserts);
                    }
                }).
                error(function(data, status) {
                    alert( "Error: " + status + " Get sections failed. Check your internet connection");
                });
        };

        $scope.usersModel = {
            addCanvasUserImg: function(user) {
                var uniqueSuffix = "?" + new Date().getTime();
                var php_script;
                php_script = "addCanvasUserImg.php";
                var params = {};
                params.canvas_user_id = user.canvas_user_id;
                $http({method: 'POST',
                    url: UserService.appDir + 'php/' + php_script + uniqueSuffix,
                    data: params,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }).
                    success(function(data) {
                        if (data) {
                            user.canvas_img = data.profile_photo;
                        }
                    }).
                    error(function(data, status) {
                        alert( "Error: " + status + " Add canvas image failed. Check your internet connection");
                    });
            },
            toggleSingleSection: function(user) {
                var uniqueSuffix = "?" + new Date().getTime();
                var php_script;
                php_script = "toggleSingleSection.php";
                var params = {};
                params.canvas_user_id = user.canvas_user_id;
                params.single_section = user.single_section;
                $http({method: 'POST',
                    url: UserService.appDir + 'php/' + php_script + uniqueSuffix,
                    data: params,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }).
                    success(function(data) {
                        if (data) {
                            //user.canvas_img = data.profile_photo;
                        }
                    }).
                    error(function(data, status) {
                        alert( "Error: " + status + " Add canvas image failed. Check your internet connection");
                    });
            }
        };

        $scope.userGridOptions = {
            showGridFooter: true,
            enableFiltering: true,
            enableSorting: true,
            rowHeight:40,
            columnDefs: [
                { field: 'user_name', name:"User Name", aggregationType: uiGridConstants.aggregationTypes.count},
                { field: 'role_id', width:100, name:"Role"},
                { field: 'net_id', width:100, name:"Net ID"},
                { field: 'section_num', width:100, name:"Section", type:"number"},
                {width:80, enableFiltering: false, enableSorting: false, name:"Image", cellTemplate:'<div class="ui-grid-cell-contents"><a class="btn btn-default btn-sm" ng-click="getExternalScopes().addCanvasUserImg(row.entity)"><i class="fa fa-cloud-upload"></i></a></div>'},
                {width:80, enableFiltering: false, enableSorting: false, name:"Only", cellTemplate:'<div class="ui-grid-cell-contents"><input type="checkbox" ng-hide="row.entity.role_id ==\'1\'" ng-true-value="\'1\'" ng-fasle-value="\'0\'" ng-model="row.entity.single_section" ng-change="getExternalScopes().toggleSingleSection(row.entity)"></div>'}
            ]
        };

    });
