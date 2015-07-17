<?php
session_start();
$_POST = json_decode(file_get_contents("php://input"), true);
if (isset($_SESSION['canvas_course_id']) && $_SESSION['priv_level']>1) {
    class DbInfo {
        var $sections;
    }

    $db_result = new DbInfo();

    $course_id = $_POST['canvas_course_id'];

    $token = “your_token”;
    $url = "https://your_canvas_url/api/v1/courses/".$course_id."/sections";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $headers = array('Authorization: Bearer ' . $token);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    //curl_setopt($ch,CURLOPT_POST, count($fields));
    //curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_query);
    $event_data = curl_exec($ch);
    curl_close($ch);

    $db_result->sections = $event_data;

    echo json_encode($db_result);
} else {
    echo json_encode("Not authenticated");
}

?>