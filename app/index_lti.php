<?php
$ok = @session_start();
if(!$ok){
    session_regenerate_id(true); // replace the Session ID
    session_start();
}
//admin only = "https://your_domain/app/announcement_master/config/all.xml";
//key = your_key
//secret = your_secret
require_once 'ims-blti/blti.php';
$context = new BLTI(“your_secret”, false, false);

if ( $context->valid ) {
    $_SESSION['canvas_course_id'] = $_POST['custom_canvas_course_id'];
    $_SESSION['course_name'] = $_POST['context_title'];
    $_SESSION['canvas_user_id'] = $_POST['custom_canvas_user_id'];
    $_SESSION['net_id'] = strtolower($_POST['custom_canvas_user_login_id']);
    $_SESSION['full_name'] = $_POST['lis_person_name_full'];
    $_SESSION['email'] = strtolower($_POST['lis_person_contact_email_primary']);
    $roles = $_POST['roles'];
    if (strpos($roles, 'Administrator') !== false) {
        $_SESSION['priv_level'] = 5;
    } else if (strpos($roles, 'Instructor') !== false) {
        $_SESSION['priv_level'] = 4;
    } else if (strpos($roles, 'Designer') !== false || strpos($roles, 'ContentDeveloper') !== false) {
        $_SESSION['priv_level'] = 3;
    } else if (strpos($roles, 'TeachingAssistant') !== false) {
        $_SESSION['priv_level'] = 2;
    } else {
        $_SESSION['priv_level'] = 1;
    }
    $_SESSION['roles'] = $roles;
} else {
    session_unset();
    session_destroy();
}
?>

<!doctype html>
<html class="no-js">
<head>
    <meta charset="utf-8">
    <title>Announcement Master</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width">
    <link rel="styleSheet" href="../bower_components/angular-ui-grid/ui-grid.min.css"/>
    <link rel="stylesheet" href="../bower_components/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../bower_components/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="libs/angular-multi-select.css">
    <link rel="stylesheet" href="styles/main.css">
</head>
<body ng-app="announcementMasterApp">
<div ng-controller="MenuCtrl">
    <nav class="navbar navbar-default navbar-fixed-top no-print" role="navigation" ng-init="user.priv_level=1" ng-show="user.priv_level>1">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#session-navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="session-navbar-collapse">
            <ul class="nav navbar-nav">
                <li ng-class="{active:route.is_admin}"><a href="" ng-click="setActive('/admin')">Announcements</a></li>
                <li ng-class="{active:route.is_users}"><a href="" ng-click="setActive('/users')">Admin & Users</a></li>
            </ul>
        </div><!-- /.navbar-collapse -->
    </nav>
</div>
<div class="container" ng-view=""></div>

<script src="../bower_components/jquery/dist/jquery.min.js"></script>
<script src="../bower_components/angular/angular.min.js"></script>
<script src="../bower_components/angular-sanitize/angular-sanitize.min.js"></script>
<script src="../bower_components/angular-animate/angular-animate.min.js"></script>
<script src="../bower_components/angular-touch/angular-touch.min.js"></script>
<script src="../bower_components/angular-route/angular-route.min.js"></script>
<script src="../bower_components/underscore/underscore-min.js"></script>
<script src="../bower_components/ng-flow/dist/ng-flow-standalone.min.js"></script>
<script src="../bower_components/angular-ui-grid/ui-grid.min.js"></script>
<script src="scripts/app.js?v=1"></script>
<script src="scripts/controllers/main.js"></script>
<script src="scripts/controllers/admin.js?v=1"></script>
<script src="scripts/controllers/menu.js"></script>
<script src="scripts/controllers/users.js?v=1"></script>
<script src="scripts/services/user.js?v=1"></script>
<script src="//tinymce.cachefly.net/4.1/tinymce.min.js"></script>
<script src="libs/angular-multi-select.js"></script>
</body>
</html>
