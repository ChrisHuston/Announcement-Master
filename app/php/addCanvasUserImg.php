<?php
session_start();
$_POST = json_decode(file_get_contents("php://input"), true);
if (isset($_SESSION['priv_level']) && $_SESSION['priv_level']>1) {
    include("advanced_user_oo.php");
    Define('DATABASE_SERVER', $hostname);
    Define('DATABASE_USERNAME', $username);
    Define('DATABASE_PASSWORD', $password);
    Define('DATABASE_NAME', 'announcement_master');

    class DbInfo {
        var $profile_photo;
    }

    $res = new DbInfo();

    $mysqli = new mysqli(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);

    $canvas_user_id = $_POST['canvas_user_id'];
    if ($canvas_user_id != '0') {
        $token = “your_token”;
        $url = "https://your_canvas_url/api/v1/users/".$canvas_user_id."/profile";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $headers = array('Authorization: Bearer ' . $token);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        //curl_setopt($ch,CURLOPT_POST, count($fields));
        //curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_query);
        $event_data = curl_exec($ch);
        curl_close($ch);
        $event_obj = json_decode( $event_data );
        $canvas_img = $event_obj->avatar_url;
    } else {
        $canvas_img = '';
    }
    $res->profile_photo = $canvas_img;

    $query = "UPDATE course_users SET canvas_img='$canvas_img'
            WHERE canvas_user_id='$canvas_user_id'";
    $result = $mysqli->query($query);
    $mysqli->close();
    echo json_encode($res);
}

?>