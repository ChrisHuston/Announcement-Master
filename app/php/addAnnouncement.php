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

    $announcement_txt = $mysqli->real_escape_string($_POST['announcement_txt']);
    $title = $mysqli->real_escape_string($_POST['title']);
    $values = $_POST['values'];
    $course_id = $_POST['course_id'];
    $net_id = $_SESSION['net_id'];

    $query = "INSERT INTO announcements
                (course_id, net_id, announcement_txt, title, post_date)
                VALUES ('$course_id', '$net_id', '$announcement_txt', '$title', UTC_TIMESTAMP())";
    $mysqli->query($query);

    $announcement_id = $mysqli->insert_id;
    $values = str_replace("id", $announcement_id, $values);

    $query = "INSERT INTO announcement_sections (announcement_id, canvas_course_id, section_id)
    VALUES ".$values;
    $result = $mysqli->query($query);

    $mysqli->close();
    //echo json_encode($query);
    echo json_encode($announcement_id);
}

?>