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
    $course_name = $mysqli->real_escape_string($_POST['course_name']);
    $course_id = $_SESSION['course_id'];

    $query = "UPDATE canvas_courses SET course_name='$course_name'
            WHERE canvas_course_id='$canvas_course_id' AND course_id='$course_id'";
    $result = $mysqli->query($query);

    $mysqli->close();
    echo json_encode($result);
}

?>