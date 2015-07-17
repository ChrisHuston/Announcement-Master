<?php
session_start();
class UserInfo {
    var $login_error = "NONE";
    var $announcements = [];
    var $announcement_sections = [];
    var $canvas_courses = [];
    var $sections = [];
    var $canvas_course_id;
    var $course_id = 0;
    var $course_name;
    var $priv_level = 1;
    var $user_name;
    var $section_id;
    var $single_section;
    var $canvas_img;
    var $roles;
    var $my_sections = [];
}

$res = new UserInfo();

$_POST = json_decode(file_get_contents("php://input"), true);
if (isset($_SESSION['canvas_course_id'])) {
    include("advanced_user_oo.php");
    Define('DATABASE_SERVER', $hostname);
    Define('DATABASE_USERNAME', $username);
    Define('DATABASE_PASSWORD', $password);
    Define('DATABASE_NAME', 'announcement_master');

    $mysqli = new mysqli(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);

    $res->priv_level = $_SESSION['priv_level'];

    $canvas_course_id = $_SESSION['canvas_course_id'];
    $net_id = $_SESSION['net_id'];
    $res->roles = $_SESSION['roles'];

    $res->canvas_course_id = $canvas_course_id;
    $res->user_name = $_SESSION['full_name'];

    if ($_SESSION['priv_level']==1) {
        if ($_SESSION['full_name'] == 'Test Student') {
            $query = "SELECT announcement_txt, title, post_date, p.user_name, p.canvas_img, p.email
            FROM announcements a
            INNER JOIN course_users p
                ON p.net_id=a.net_id AND p.course_id=a.course_id
            INNER JOIN course_users u
                ON u.canvas_course_id='$canvas_course_id'
            INNER JOIN announcement_sections s
                ON s.announcement_id=a.announcement_id AND s.section_id=u.section_id
            GROUP BY a.announcement_id
            ORDER BY post_date DESC";
        } else {
            $query = "SELECT announcement_txt, title, post_date, p.user_name, p.canvas_img, p.email
            FROM announcements a
            INNER JOIN course_users p
                ON p.net_id=a.net_id AND p.course_id=a.course_id
            INNER JOIN course_users u
                ON u.net_id='$net_id' AND u.canvas_course_id='$canvas_course_id'
            INNER JOIN announcement_sections s
                ON s.announcement_id=a.announcement_id AND s.section_id=u.section_id
            GROUP BY a.announcement_id
            ORDER BY post_date DESC";
        }



        $result = $mysqli->query($query);
        $json = array();
        while ($row = $result->fetch_assoc()) {
            $json[] = $row;
        }
        $res->announcements = $json;
    } else {
        $query = "SELECT c.course_id, c.course_name, u.section_id, IFNULL(u.single_section,0), u.canvas_img
        FROM canvas_courses cc
        INNER JOIN courses c
            ON c.course_id=cc.course_id
        LEFT JOIN course_users u
            ON u.net_id='$net_id' AND u.course_id=c.course_id
        WHERE cc.canvas_course_id='$canvas_course_id'
        ORDER BY u.single_section DESC
        LIMIT 1";
        $result = $mysqli->query($query);
        list($course_id, $course_name, $section_id, $single_section, $canvas_img) = $result->fetch_row();

        if ($course_id != null) {
            $res->section_id = $section_id;
            $res->canvas_img = $canvas_img;
            if (is_null($single_section)) {
                $single_section = '0';
            }
            $res->single_section = $single_section;


            $query = "SELECT canvas_course_id, course_name
                FROM canvas_courses
                WHERE course_id='$course_id'
                ORDER BY canvas_course_id; ";

            $query .= "SELECT s.section_id, s.section_num, s.canvas_course_id, COUNT(u.net_id) AS num_members
                FROM course_sections s
                INNER JOIN canvas_courses c
                    ON c.canvas_course_id=s.canvas_course_id
                LEFT JOIN course_users u
                    ON u.section_id=s.section_id
                WHERE c.course_id='$course_id'
                GROUP BY s.section_id
                ORDER BY s.section_num; ";

            $query .= "SELECT a.announcement_txt, a.title, a.announcement_id, a.post_date, IFNULL(p.user_name, 'Admin') AS user_name, p.canvas_img, p.email
                FROM announcements a
                INNER JOIN announcement_sections s
                    ON a.announcement_id=s.announcement_id AND ('$single_section'='0' OR s.section_id IN (SELECT section_id FROM course_users WHERE net_id='$net_id'))
                LEFT JOIN course_users p
                    ON p.net_id=a.net_id AND p.course_id=a.course_id
                WHERE a.course_id='$course_id'
                GROUP BY a.announcement_id
                ORDER BY post_date DESC; ";

            $query .= "SELECT s.announcement_id, s.section_id, s.canvas_course_id FROM
                announcement_sections s
                INNER JOIN courses c
                    ON c.course_id='$course_id'
				INNER JOIN canvas_courses r
					ON r.course_id=c.course_id AND s.canvas_course_id=r.canvas_course_id
                INNER JOIN course_sections t
                    ON t.canvas_course_id=r.canvas_course_id AND s.canvas_course_id=t.canvas_course_id AND t.section_id=s.section_id
				GROUP BY s.announcement_id, s.section_id
                ORDER BY s.announcement_id, s.section_id; ";

            if ($single_section != 0) {
                $query .= "SELECT u.section_id, u.single_section, u.single_section, s.section_num
                FROM course_users u
                INNER JOIN course_sections s
                    ON s.section_id=u.section_id
                WHERE u.net_id='$net_id' AND u.course_id='$course_id'
                ORDER BY s.section_num; ";
            }


            $result = $mysqli->multi_query($query);


            if ($mysqli->more_results()) {
                $mysqli->next_result();
                $result = $mysqli->store_result();
                $json = array();
                while ($row = $result->fetch_assoc()) {
                    $json[] = $row;
                }
                $res->canvas_courses = $json;
            }

            if ($mysqli->more_results()) {
                $mysqli->next_result();
                $result = $mysqli->store_result();
                $json = array();
                while ($row = $result->fetch_assoc()) {
                    $json[] = $row;
                }
                $res->sections = $json;
            }

            if ($mysqli->more_results()) {
                $mysqli->next_result();
                $result = $mysqli->store_result();
                $json = array();
                while ($row = $result->fetch_assoc()) {
                    $json[] = $row;
                }
                $res->announcements = $json;
            }

            if ($mysqli->more_results()) {
                $mysqli->next_result();
                $result = $mysqli->store_result();
                $json = array();
                while ($row = $result->fetch_assoc()) {
                    $json[] = $row;
                }
                $res->announcement_sections = $json;
            }

            if ($mysqli->more_results()) {
                $mysqli->next_result();
                $result = $mysqli->store_result();
                $json = array();
                while ($row = $result->fetch_assoc()) {
                    $json[] = $row;
                }
                $res->my_sections = $json;
            }


        } else {
            $course_name = $mysqli->real_escape_string($_SESSION['course_name']);
            $query = "INSERT INTO courses (course_name) VALUES ('$course_name')";
            $mysqli->query($query);
            $course_id = $mysqli->insert_id;
            $json = array();
            $json[] = array("canvas_course_id" => $canvas_course_id, "course_name" => $_SESSION['course_name']);
            $res->canvas_courses = $json;
            $query = "INSERT INTO canvas_courses (course_id, canvas_course_id, course_name) VALUES
                ('$course_id', '$canvas_course_id', '$course_name')";
            $mysqli->query($query);
        }
        $_SESSION['course_id'] = $course_id;
        $res->course_id = $course_id;
        $res->course_name = $course_name;
    }

    $mysqli->close();
    echo json_encode($res);

} else {
    $res->login_error = "Authentication error.";
    echo json_encode($res);
}

?>