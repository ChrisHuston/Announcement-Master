'use strict';

/**
 * @ngdoc function
 * @name announcementMasterApp.controller:AdminCtrl
 * @description
 * # AdminCtrl
 * Controller of the announcementMasterApp
 */
angular.module('announcementMasterApp')
  .controller('AdminCtrl', function ($scope, UserService, $timeout, $http) {
        $scope.admin = UserService.admin;
        $scope.user = UserService.user;
        $scope.currentAnnouncement = {};
        $scope.selectedAnnouncement = null;
        $scope.email_edited = false;
        $scope.selectedSections = [];

        $scope.flow_config = {
            target: '/app/announcement_master/php/fileUpload.php',
            testChunks:false,
            query:{'course_id': UserService.admin.course_id}
        };

        var ed;
        $timeout(function() {
            ed = new tinymce.Editor('announcement_txt', {
                selector: "textarea",
                menubar: false,
                statusbar: true,
                height: 250,
                relative_urls: false,
                remove_script_host: false,
                extended_valid_elements : "a[class|href|id|download|type|target|target=_blank],i[class|style]",
                target_list: [
                    {title: 'New page', value: '_blank'},
                    {title: 'Same page', value: '_self'}
                ],
                auto_focus: "announcement_txt",
                content_css : "https://www.kblocks.com/app/styles/tiny_style.css",
                formats: {moderntable : {selector : 'table', classes: 'table table-condensed table-bordered table-striped'}},
                plugins: [
                    "advlist autolink lists link charmap preview",
                    "searchreplace visualblocks code",
                    "insertdatetime table paste textcolor"
                ],
                toolbar: "bold italic underline | subscript superscript | styleselect | bullist numlist table outdent indent | link charmap code preview"
            }, tinymce.EditorManager);

            ed.render();
            //tinymce.get('role_description').setContent($scope.assignment.review_rubric);
        }, 500);

        $scope.formatTables = function() {
            tinyMCE.activeEditor.selection.select(ed.getBody(), true);
            tinyMCE.activeEditor.formatter.apply('moderntable');
            tinyMCE.activeEditor.selection.select();
        };


        $scope.addAnnouncement = function() {
            if ($scope.admin.single_section) {
                $scope.selectedSections = [{canvas_course_id:$scope.admin.canvas_course_id, section_id:$scope.admin.section_id}];
            }
            if (!$scope.currentAnnouncement.title) {
                alert("Enter a title for the announcement.");
                return;
            }
            var announcement_txt = tinyMCE.activeEditor.getContent();
            var values = "";
            var email_sections = "(";
            angular.forEach($scope.selectedSections, function(s) {
                values += '(id,' + s.canvas_course_id + ',' + s.section_id + '),';
                email_sections += "'" + s.section_id + "',";
            });
            if (values === "") {
                alert("Select at least one section for the announcement.");
                return;
            }
            $scope.currentAnnouncement.progress = "sending";
            $scope.currentAnnouncement.announcement_txt = announcement_txt;
            values = values.slice(0,-1);
            email_sections = email_sections.slice(0,-1);
            email_sections += ")";

            var uniqueSuffix = "?" + new Date().getTime();
            var php_script;
            php_script = "addAnnouncement.php";
            var params = {};
            params.values = values;
            params.announcement_txt = announcement_txt;
            params.title = $scope.currentAnnouncement.title;
            params.course_id = UserService.admin.course_id;
            $http({method: 'POST',
                url: UserService.appDir + 'php/' + php_script + uniqueSuffix,
                data: params,
                headers: {'Content-Type': 'application/x-www-form-urlencoded'}
            }).
                success(function(data) {
                    $scope.currentAnnouncement.announcement_id = data;
                    $scope.currentAnnouncement.sections = UserService.initAnnouncementSections($scope.selectedSections);
                    $scope.currentAnnouncement.user_name = UserService.user.user_name;
                    $scope.currentAnnouncement.post_date = new Date();
                    $scope.currentAnnouncement.canvas_img = $scope.admin.canvas_img;
                    $scope.user.announcements.unshift($scope.currentAnnouncement);
                    UserService.emailAnnouncement(email_sections, $scope.currentAnnouncement, $scope.currentAnnouncement);
                }).
                error(function(data, status) {
                    alert( "Error: " + status + " Add announcement failed. Check your internet connection");
                });
        };

        $scope.selectAnnouncement = function(announcement) {
            $scope.selectedSections = [];
            angular.forEach($scope.admin.sections, function(s) {
                if (_.findWhere(announcement.sections, {section_id: s.section_id, can_view:true})) {
                    s.ticked = true;
                    $scope.selectedSections.push(s);
                } else {
                    s.ticked = false;
                }
            });
            tinymce.get('announcement_txt').setContent(announcement.announcement_txt);
            $scope.selectedAnnouncement = announcement;
            $scope.currentAnnouncement.title = announcement.title;
            $scope.currentAnnouncement.delivered = announcement.delivered;
        };

        $scope.toggleShowAnnouncement = function(a) {
            a.show_announcement = !a.show_announcement;
        };

        $scope.toggleAnnouncementSection = function(announcement, section) {
            announcement.progress = "sending";
            announcement.delivered = null;
            var uniqueSuffix = "?" + new Date().getTime();
            var php_script;
            php_script = "toggleAnnouncementSection.php";
            var params = {};
            params.canvas_course_id = section.canvas_course_id;
            params.section_id = section.section_id;
            params.announcement_id = announcement.announcement_id;
            params.can_view = section.can_view?1:0;
            $http({method: 'POST',
                url: UserService.appDir + 'php/' + php_script + uniqueSuffix,
                data: params,
                headers: {'Content-Type': 'application/x-www-form-urlencoded'}
            }).
                success(function() {
                    if (section.can_view) {
                        var email_sections = "('" + section.section_id + "')";
                        UserService.emailAnnouncement(email_sections, announcement);
                    }

                }).
                error(function(data, status) {
                    alert( "Error: " + status + " Toggle announcement section failed. Check your internet connection");
                });
        };

        $scope.saveAnnouncement = function() {
            if ($scope.admin.single_section) {
                $scope.selectedSections = [{canvas_course_id:$scope.admin.canvas_course_id, section_id:$scope.admin.section_id}];
            }
            var announcement_txt = tinyMCE.activeEditor.getContent();
            var announcement_id = $scope.selectedAnnouncement.announcement_id;
            $scope.selectedAnnouncement.announcement_txt = announcement_txt;
            $scope.selectedAnnouncement.title = $scope.currentAnnouncement.title;
            $scope.selectedAnnouncement.post_date = new Date();
            if ($scope.email_edited) {
                $scope.currentAnnouncement.progress = "sending";
                $scope.currentAnnouncement.delivered = null;
            }
            var uniqueSuffix = "?" + new Date().getTime();
            var php_script;
            php_script = "saveAnnouncement.php";
            var params = {};
            params.announcement_txt = announcement_txt;
            params.title = $scope.currentAnnouncement.title;
            params.announcement_id = announcement_id;
            $http({method: 'POST',
                url: UserService.appDir + 'php/' + php_script + uniqueSuffix,
                data: params,
                headers: {'Content-Type': 'application/x-www-form-urlencoded'}
            }).
                success(function() {
                    if ($scope.email_edited) {
                        var email_sections = "(";
                        angular.forEach($scope.selectedSections, function(s) {
                            email_sections += "'" + s.section_id + "',";
                        });
                        if (email_sections === "(") {
                            return;
                        }
                        email_sections = email_sections.slice(0,-1);
                        email_sections += ")";
                        UserService.emailAnnouncement(email_sections, $scope.selectedAnnouncement, $scope.currentAnnouncement);
                    }
                }).
                error(function(data, status) {
                    alert( "Error: " + status + " Save announcement failed. Check your internet connection");
                });
        };

        $scope.deleteAnnouncement = function() {
            var announcement_id = $scope.selectedAnnouncement.announcement_id;

            var uniqueSuffix = "?" + new Date().getTime();
            var php_script;
            php_script = "deleteAnnouncement.php";
            var params = {};
            params.announcement_id = announcement_id;
            $http({method: 'POST',
                url: UserService.appDir + 'php/' + php_script + uniqueSuffix,
                data: params,
                headers: {'Content-Type': 'application/x-www-form-urlencoded'}
            }).
                success(function(data) {
                    if (data) {
                        for (var i=0; i < UserService.user.announcements.length; i++) {
                            if (UserService.user.announcements[i].announcement_id===announcement_id) {
                                UserService.user.announcements.splice(i,1);
                                break;
                            }
                        }
                    }
                }).
                error(function(data, status) {
                    alert( "Error: " + status + " Save announcement failed. Check your internet connection");
                });
        };

        var safe_name;
        $scope.addedUpload = function(flowFile) {
            safe_name = flowFile.name;
            safe_name = safe_name.replace(/ /g, '_');
            safe_name = safe_name.replace(/#/g, '');
            flowFile.name = safe_name;
            flowFile.flowObj.query = {'course_id':UserService.admin.course_id};
        };


        $scope.uploadComplete = function(file_type) {
            var full_path = "https://www.kblocks.com/app/announcement_master/files/" + UserService.admin.course_id + "/" + safe_name;
            var tag;
            if (file_type === 'video') {
                tag = "<video class=\"t-wiki-video video-js vjs-default-skin vjs-big-play-centered img-responsive\" src=\"" + full_path + "\" type=\"video/mp4\" controls></video>";
            } else if (file_type === 'img') {
                tag = "<img src=\"" + full_path + "\" class=\"img-responsive\" alt=\"" + safe_name + "\"></img>";
            } else {
                tag = "<a href=\"" + full_path + "\" target=\"_blank\">" + safe_name + "</a>";
            }
            tinyMCE.activeEditor.execCommand('mceInsertContent', false, tag);
        }
  });
