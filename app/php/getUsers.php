<?php
session_start();
$_POST = json_decode(file_get_contents("php://input"), true);
if (isset($_SESSION['course_id'])) {
    include("advanced_user_oo.php");
    Define('DATABASE_SERVER', $hostname);
    Define('DATABASE_USERNAME', $username);
    Define('DATABASE_PASSWORD', $password);
    Define('DATABASE_NAME', 'announcement_master');
    $mysqli = new mysqli(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);

    class DbInfo {
        var $users = [];
    }

    $res = new DbInfo();

    $course_id = $_POST['course_id'];

    $query = "SELECT u.net_id, u.user_name, u.email, u.role_id, u.section_id, u.canvas_user_id,
            u.canvas_course_id, s.section_num, u.canvas_img, single_section
        FROM course_users u
        INNER JOIN course_sections s
            ON s.section_id=u.section_id
        WHERE u.course_id='$course_id'
        ORDER BY s.section_num, u.user_name";
    $result = $mysqli->query($query);
    $json = array();
    while ($row = $result->fetch_assoc()) {
        $json[] = $row;
    }
    $res->users = $json;

    $mysqli->close();
    echo json_encode($res);
}

?>