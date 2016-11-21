<?php
class Courses extends CI_Controller
{
    public function __construct(){
        parent::__construct();
        $this->load->library("session");
        $this->load->database();
    }
    
    public function getCoursesList()
    {
        header("Content-Type: application/json", true);
        $rs = $this->db->get('courses')->result();
        echo json_encode($rs);
    }
}
?>