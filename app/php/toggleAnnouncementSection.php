<?php
session_start();
$_POST = json_decode(file_get_contents("php://input"), true);
if (isset($_SESSION['priv_level']) && $_SESSION['priv_level']>1) {
    include("advanced_user_oo.php");
    Define('DATABASE_SERVER', $hostname);
    Define('DATABASE_USERNAME', $username);
    Define('DATABASE_PASSWORD', $password);
    Define('DATABASE_NAME', 'announcement_master');
    $mysqli = new mysqli(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);

    $announcement_id = $_POST['announcement_id'];
    $canvas_course_id = $_POST['canvas_course_id'];
    $section_id = $_POST['section_id'];
    $can_view = $_POST['can_view'];

    if ($can_view == 1) {
        $query = "INSERT INTO announcement_sections (announcement_id, canvas_course_id, section_id)
        VALUES ('$announcement_id','$canvas_course_id','$section_id')";
    } else {
        $query = "DELETE FROM announcement_sections
        WHERE announcement_id='$announcement_id' AND canvas_course_id='$canvas_course_id' AND section_id='$section_id'";
    }
    $result = $mysqli->query($query);

    $mysqli->close();
    echo json_encode($result);
}

?>