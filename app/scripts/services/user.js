'use strict';

/**
 * @ngdoc service
 * @name announcementMasterApp.User
 * @description
 * # User
 * Factory in the announcementMasterApp.
 */
angular.module('announcementMasterApp')
    .factory('UserService', function ($http, $location) {
        var userInstance = {};

        userInstance.appDir = '/app/announcement_master/';

        userInstance.admin = {sections:[], canvas_courses:[], course_id:null, course_name:null, single_section:false, section_id:null, canvas_img:null};
        userInstance.user = {loginError:null, priv_level:1, announcements:[]};
        userInstance.route = {is_admin:true, is_users:false};

        userInstance.initAnnouncementSections = function(announcement_sections) {
            var sections = [];
            angular.forEach(userInstance.admin.sections, function(s) {
                var section = {section_num: s.section_num, can_view:false, section_id: s.section_id, canvas_course_id: s.canvas_course_id};
                for (var i = 0; i < announcement_sections.length; i++) {
                    if (parseInt(announcement_sections[i].section_id) === parseInt(s.section_id)) {
                        section.can_view = true;
                        break;
                    }
                }
                sections.push(section);
            });
            return sections;
        };

        var login = function() {
            var uniqueSuffix = "?" + new Date().getTime();
            var php_script = "lti_login.php";
            var params = {};
            $http({method: 'POST',
                url: userInstance.appDir + 'php/' + php_script + uniqueSuffix,
                data: params,
                headers: {'Content-Type': 'application/x-www-form-urlencoded'}
            }).
                success(function(data) {
                    if (data.login_error === "NONE") {
                        userInstance.user.priv_level = parseInt(data.priv_level);
                        userInstance.user.user_name = data.user_name;
                        if (userInstance.user.priv_level > 1) {
                            userInstance.admin.canvas_courses = data.canvas_courses;
                            userInstance.admin.course_id = data.course_id;
                            userInstance.admin.canvas_course_id = data.canvas_course_id;
                            userInstance.admin.course_name = data.course_name;
                            userInstance.admin.single_section = data.single_section === '1';
                            userInstance.admin.section_id = data.section_id;
                            userInstance.admin.canvas_img = data.canvas_img;
                            if (data.sections) {
                                angular.forEach(data.sections, function(s) {
                                    s.section_num = parseInt(s.section_num);
                                    s.section_name = 'Section ' + s.section_num;

                                });
                                userInstance.admin.sections = data.sections;
                            }
                            if (userInstance.admin.single_section && data.my_sections) {
                                angular.forEach(data.my_sections, function(s) {
                                    s.section_num = parseInt(s.section_num);
                                    s.section_name = 'Section ' + s.section_num;
                                });
                                userInstance.admin.sections = data.my_sections;
                            }
                            angular.forEach(data.announcements, function(a) {
                                a.post_date = utcStrToLocalDate(a.post_date);
                                a.show_announcement = false;
                                var names = a.user_name.split(",");
                                if (names.length > 1) {
                                    a.user_name = names[1] + ' ' + names[0];
                                } else {
                                    a.user_name = names[0];
                                }

                                var announcement_sections = _.where(data.announcement_sections, {announcement_id: a.announcement_id});
                                a.sections = userInstance.initAnnouncementSections(announcement_sections);
                            });
                            userInstance.user.announcements = data.announcements;
                            $location.path('/admin');
                        } else {
                            angular.forEach(data.announcements, function(a) {
                                var names = a.user_name.split(",");
                                a.user_name = names[1] + ' ' + names[0];
                                a.post_date = utcStrToLocalDate(a.post_date);
                                a.show_announcement = false;
                            });
                            $("body").css('padding-top',0);
                            userInstance.user.announcements = data.announcements;
                            $location.path('/');
                        }
                    } else {
                        userInstance.user.loginError =  data.login_error;
                    }
                }).
                error(function(data, status) {
                    userInstance.user.loginError =  "Error: " + status + " Sign-in failed. Check your internet connection";
                });
        };

        login();


        userInstance.addCanvasCourse = function(course) {
            var uniqueSuffix = "?" + new Date().getTime();
            var php_script = "addCanvasCourse.php";
            var params = {};
            params.canvas_course_id = course.canvas_course_id;
            params.course_name = course.course_name;
            params.course_id = userInstance.admin.course_id;
            $http({method: 'POST',
                url: userInstance.appDir + 'php/' + php_script + uniqueSuffix,
                data: params,
                headers: {'Content-Type': 'application/x-www-form-urlencoded'}
            }).
                success(function(data) {
                    if (data) {
                        userInstance.admin.canvas_courses.push(params);
                    }
                }).
                error(function(data, status) {
                    alert("Error: " + status + " Add course failed. Check your internet connection");
                });
        };

        userInstance.changeSectionNumber = function(s) {
            var uniqueSuffix = "?" + new Date().getTime();
            var php_script = "changeSectionNumber.php";
            var params = {};
            params.canvas_course_id = s.canvas_course_id;
            params.section_id = s.section_id;
            params.section_num = s.section_num;
            $http({method: 'POST',
                url: userInstance.appDir + 'php/' + php_script + uniqueSuffix,
                data: params,
                headers: {'Content-Type': 'application/x-www-form-urlencoded'}
            }).
                success(function(data) {
                    if (!data) {
                        alert("Change section number failed.");
                    }
                }).
                error(function(data, status) {
                    alert("Error: " + status + " Change section number failed. Check your internet connection");
                });
        };

        userInstance.deleteSection = function(s) {
            var uniqueSuffix = "?" + new Date().getTime();
            var php_script = "deleteSection.php";
            var params = {};
            params.canvas_course_id = s.canvas_course_id;
            params.section_id = s.section_id;
            $http({method: 'POST',
                url: userInstance.appDir + 'php/' + php_script + uniqueSuffix,
                data: params,
                headers: {'Content-Type': 'application/x-www-form-urlencoded'}
            }).
                success(function(data) {
                    if (!data) {
                        alert("Delete section failed.");
                    }
                }).
                error(function(data, status) {
                    alert("Error: " + status + " Delete section failed. Check your internet connection");
                });
        };

        userInstance.updateCanvasCourse = function(course) {
            var uniqueSuffix = "?" + new Date().getTime();
            var php_script = "updateCanvasCourse.php";
            var params = {};
            params.canvas_course_id = course.canvas_course_id;
            params.course_name = course.course_name;
            $http({method: 'POST',
                url: userInstance.appDir + 'php/' + php_script + uniqueSuffix,
                data: params,
                headers: {'Content-Type': 'application/x-www-form-urlencoded'}
            }).
                success(function(data) {
                    if (data) {
                        for (var i=0; i < userInstance.admin.canvas_courses.length; i++) {
                            if (userInstance.admin.canvas_courses[i].canvas_course_id===course.canvas_course_id) {
                                userInstance.admin.canvas_courses[i].course_name = course.course_name;
                                break;
                            }
                        }
                    }
                }).
                error(function(data, status) {
                    alert("Error: " + status + " Update course failed. Check your internet connection");
                });
        };

        userInstance.deleteCanvasCourse = function(course) {
            var uniqueSuffix = "?" + new Date().getTime();
            var php_script = "deleteCanvasCourse.php";
            var params = {};
            params.canvas_course_id = course.canvas_course_id;
            $http({method: 'POST',
                url: userInstance.appDir + 'php/' + php_script + uniqueSuffix,
                data: params,
                headers: {'Content-Type': 'application/x-www-form-urlencoded'}
            }).
                success(function(data) {
                    if (data) {
                        for (var i=0; i < userInstance.admin.canvas_courses.length; i++) {
                            if (userInstance.admin.canvas_courses[i].canvas_course_id===course.canvas_course_id) {
                                userInstance.admin.canvas_courses.splice(i,1);
                                break;
                            }
                        }
                    }
                }).
                error(function(data, status) {
                    alert("Error: " + status + " Delete course failed. Check your internet connection");
                });
        };

        userInstance.updateCourse = function(course_name) {
            var uniqueSuffix = "?" + new Date().getTime();
            var php_script = "updateCourse.php";
            var params = {};
            params.course_id = userInstance.admin.course_id;
            params.course_name = course_name;
            $http({method: 'POST',
                url: userInstance.appDir + 'php/' + php_script + uniqueSuffix,
                data: params,
                headers: {'Content-Type': 'application/x-www-form-urlencoded'}
            }).
                success(function() {
                }).
                error(function(data, status) {
                    alert("Error: " + status + " Update course failed. Check your internet connection");
                });
        };

        userInstance.emailAnnouncement = function(email_sections, announcement, status) {
            var uniqueSuffix = "?" + new Date().getTime();
            var php_script = "emailAnnouncement.php";
            var params = {};
            params.email_sections = email_sections;
            params.announcement_txt = announcement.announcement_txt;
            params.announcement_id = announcement.announcement_id;
            params.title = announcement.title;
            params.course_name = userInstance.admin.course_name;
            params.course_id = userInstance.admin.course_id;
            $http({method: 'POST',
                url: userInstance.appDir + 'php/' + php_script + uniqueSuffix,
                data: params,
                headers: {'Content-Type': 'application/x-www-form-urlencoded'}
            }).
                success(function(data) {
                    data = data.replace(/\"/g,'');
                    console.log(data);
                    status.delivered = data;
                    status.progress = null;
                }).
                error(function(data, status) {
                    alert("Error: " + status + " Email announcement failed. Check your internet connection");
                });
        };

        return userInstance;
    });


function utcStrToLocalDate(dtstr) {
    if (dtstr) {
        var utcDate;
        if (angular.isDate(dtstr)) {
            utcDate  = dtstr;
        } else {
            var year = dtstr.substr(0,4);
            var month = (dtstr.substr(5,2)-1);
            var day = dtstr.substr(8,2);
            utcDate = new Date(year, month, day, dtstr.substr(11,2), dtstr.substr(14,2), dtstr.substr(17,2)).getTime();
        }
        var offset = new Date().getTimezoneOffset() * 60 * 1000;
        return new Date(utcDate - offset);
    } else {
        return null;
    }
}