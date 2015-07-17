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

    $canvas_course_id = $_POST['canvas_course_id'];
    $section_id = $_POST['section_id'];

    $query = "DELETE FROM announcement_sections
            WHERE canvas_course_id='$canvas_course_id' AND section_id='$section_id'; ";
    $query .= "DELETE FROM course_users
            WHERE canvas_course_id='$canvas_course_id' AND section_id='$section_id'; ";
    $query .= "DELETE FROM course_sections
            WHERE canvas_course_id='$canvas_course_id' AND section_id='$section_id'; ";
    $result = $mysqli->multi_query($query);

    $mysqli->close();
    echo json_encode($result);
}

?>