<?php
session_start();
$_POST = json_decode(file_get_contents("php://input"), true);
if (isset($_SESSION['net_id']) && isset($_SESSION['priv_level']) && $_SESSION['priv_level']>2) {
    include("advanced_user_oo.php");
    require("PHPMailerAutoload.php");

    Define('DATABASE_SERVER', $hostname);
    Define('DATABASE_USERNAME', $username);
    Define('DATABASE_PASSWORD', $password);
    Define('DATABASE_NAME', 'announcement_master');

    $mysqli = new mysqli(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);

    $announcement_txt = $_POST['announcement_txt'];
    $title = $_POST['title'];
    $announcement_id = $_POST['announcement_id'];
    $course_name = $_POST['course_name'];
    $email_sections = $_POST['email_sections'];
    $subject = $course_name." Announcement";
    $sender = $_SESSION['email'];
    $sender_name = $_SESSION['full_name'];
    $course_id = $_POST['course_id'];

    $mail = new PHPMailer;
    $mail->isSMTP();
    $mail->Host       = "localhost";      // sets the SMTP server
    $mail->Port       = 25;               // set the SMTP port
    $mail->SMTPAuth   = false;            // disable SMTP authentication

    $mail->From = $sender;
    $mail->FromName = $sender_name;
    $mail->addReplyTo($sender, $sender_name);
    $mail->WordWrap   = 60;
    $mail->isHTML(true);
    $mail->Subject    = $subject;
    $mail->AltBody = "";

    $query = "SELECT user_name, email, u.canvas_course_id
        FROM course_users u
        INNER JOIN announcement_sections s
            ON s.canvas_course_id=u.canvas_course_id AND s.section_id=u.section_id
        WHERE u.course_id='$course_id' AND s.announcement_id='$announcement_id' AND s.section_id in ".$email_sections." GROUP BY u.canvas_user_id ORDER BY u.canvas_user_id; ";
    $result = $mysqli->query($query);
    /*
    $sent_mail = "";
    while (list($user_name, $user_email, $canvas_course_id) = $result->fetch_row()) {
        $sent_mail .= $user_email . "; ";
    }
    */
    $sent_mail = 0;
    $prev_course_id = '';
    $is_new_course = true;
    while (list($user_name, $user_email, $canvas_course_id) = $result->fetch_row()) {
        $is_new_course = $prev_course_id != $canvas_course_id;
        if ($is_new_course && $prev_course_id != '') {
            $mail->send();
            $mail->clearAddresses();
            $mail->clearBCCs();
            $mail->addAddress($canvas_course_id.'@kblocks.com',$course_name);
        }
        if ($is_new_course) {
            $body = "<h3>".$course_name." Announcement</h3>";
            $body .= "<h4>".$title."</h4>";
            $body .= $announcement_txt;
            $body .= '<p><a href="https://canvas.dartmouth.edu/courses/'.$canvas_course_id.'">'.$course_name.'</a></p>';
            $mail->Body = $body;
            $prev_course_id = $canvas_course_id;
            $mail->addAddress($canvas_course_id.'@kblocks.com',$course_name);
        }
        $mail->addBCC($user_email,$user_name);
        $sent_mail = $sent_mail + 1;
    }
    if ($sent_mail > 0) {
        $mail->send();
        $mail->clearAddresses();
        $mail->clearBCCs();
    }
    $mysqli->close();
    echo json_encode($sent_mail);
}

?>