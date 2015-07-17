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

    $canvas_course_id = $mysqli->real_escape_string($_POST['canvas_course_id']);
    $section_num = $mysqli->real_escape_string($_POST['section_num']);
    $section_id = $_POST['section_id'];

    $query = "UPDATE course_sections SET section_num='$section_num'
            WHERE canvas_course_id='$canvas_course_id' AND section_id='$section_id'";
    $result = $mysqli->query($query);

    $mysqli->close();
    echo json_encode($result);
}

?>