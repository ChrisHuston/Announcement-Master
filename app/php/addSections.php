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

    $inserts = $_POST['inserts'];
    $query = "INSERT IGNORE INTO course_sections
                (canvas_course_id, section_num, section_id)
                VALUES ".$inserts;
    $result = $mysqli->query($query);

    $mysqli->close();
    echo json_encode($result);
}

?>