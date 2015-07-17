<?php
session_start();
$_POST = json_decode(file_get_contents("php://input"), true);
if (isset($_SESSION['canvas_course_id'])) {
    function parse_headers( $header ){
        $retVal = array();
        $fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $header));
        foreach( $fields as $field ) {
            if( preg_match('/([^:]+): (.+)/m', $field, $match) ) {
                $match[1] = preg_replace('/(?<=^|[\x09\x20\x2D])./e', 'strtoupper("\0")', strtolower(trim($match[1])));
                if( isset($retVal[$match[1]]) ) {
                    $retVal[$match[1]] = array($retVal[$match[1]], $match[2]);
                } else {
                    $retVal[$match[1]] = trim($match[2]);
                }
            }
        }
        return $retVal;
    }

    function get_api_data($url, $ch){
        $token = “your_token”;
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_VERBOSE, 1); //Requires to load headers
        curl_setopt($ch, CURLOPT_HEADER, 1);  //Requires to load headers
        $headers = array('Authorization: Bearer ' . $token);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);

        #Parse header information from body response
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $canvas_header = substr($result, 0, $header_size);
        $body = substr($result, $header_size);
        $data = json_decode($body);

        #Parse Link Information
        $header_info = parse_headers($canvas_header);


        if(isset($header_info['Link'])){
            $links = explode(',', $header_info['Link']);
            foreach ($links as $value) {
                if (preg_match('/^\s*<(.*?)>;\s*rel="(.*?)"/', $value, $match)) {
                    $links[$match[2]] = $match[1];
                }
            }
        }

        #Check for Pagination
        if(isset($links['next'])){
            $next_data = get_api_data($links['next'], $ch);
            $data = array_merge($data,$next_data);
            return $data;
        }else{
            return $data;
        }
    }

    class DbInfo {
        var $users;
    }

    $db_result = new DbInfo();
    $course_id = $_POST['canvas_course_id'];

    $url = "https://your_canvas_url/api/v1/courses/".$course_id."/enrollments?per_page=100";
    $ch = curl_init();

    $db_result->users = get_api_data($url, $ch);
    curl_close($ch);
    echo json_encode($db_result);
} else {
    echo json_encode("Not authenticated");
}

?>