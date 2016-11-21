<?php

class UrlFetch
{
   function __construct($opt)
    {
        $this->ch     = curl_init();
        curl_setopt($this->ch, CURLOPT_URL, $opt->url);
        if(isset($opt->cookie))
        {
            $this->cookie = $opt->cookie;
            curl_setopt($this->ch, CURLOPT_COOKIEFILE, $this->cookie);
            curl_setopt($this->ch, CURLOPT_COOKIEJAR,  $this->cookie);
        }
        if(isset($opt->post))
        {
            curl_setopt($this->ch, CURLOPT_POST, count($opt->data));
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $opt->data); 
        }
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        
    } 
    
    function fetch(){$data =curl_exec($this->ch); curl_close($this->ch); return $data;}
    
    function fixCookie()
    {
        $cookie  = file_get_contents($this->cookie);
        $cookie  =  preg_replace('/\#HttpOnly\_/',  '', $cookie);
        file_put_contents($this->cookie, $cookie);
    }
    
}



class Vtcpayment 
{
    function __construct()
    {
        $this->user = '0989335791';
        $this->pass = 'Mrtung97';
        $this->cookie = '/tmp/cookie-vtc-pay.txt';
    }
    
    function getCaptcha()
    {
        // Nếu chưa đăng nhập
        
        if(true)//!file_exists($this->cookie))
        {

            $opt = (object)
            [
            'url'   => 'https://365.vtc.vn/v2/Account/Login',
            'data'  =>
                [
                 "userName"   => $this->user,
                 "password"   => $this->pass,
                 "captcha"    => "",
                 "RememberMe" => false,
                 "token"      => ""
                ],
            'post' => true,
            'cookie' => $this->cookie
            ];
            $curl = new Urlfetch($opt);
            $curl->fetch();
            $curl->fixCookie();
        };

        $opt = (object)['url' => 'https://365.vtc.vn/v2/api/Captcha/Get'];
        $curl = new Urlfetch($opt);
        return $curl->fetch();
    }
    
    function charging()
    {
        $opt = (object)[
        'url' => 'https://365.vtc.vn/v2/BuyCard/PostRechargeByCard',
        'data' => 
            [
            'CardSerial' =>  $_POST['serial'],
            'CardCode'   =>  $_POST['code'],
            'CardType'   =>  $_POST['type'],
            'Captcha'    =>  $_POST['captcha'],
            'Token'      =>  $_POST['token']
            ],
        'post' => true,
        'cookie' => $this->cookie
        ];
        
        $curl = new Urlfetch($opt);
        return $curl->fetch();  
       
    }    
}



class Users extends CI_Controller
{
    public function __construct(){
        parent::__construct();
        $this->load->library("session");
        $this->load->database();
    }
    
    public function index()
    {
        $this->load->view('users');
    }
    
    public function loginAs()
    {
        $email = $_POST['email'];
        $id    = $_POST['id'];
        
        $vaild = new UrlFetch((object)['url'=>'https://www.googleapis.com/oauth2/v3/tokeninfo?id_token='.$id]);
        $rs = json_decode($vaild->fetch());
        if($rs->email != $email){ die(); };
        
        
        $this->db->update('users', ['session' => '-'] , ['session' => session_id()]);
        $query = $this->db->get_where('users', array('email' => $email));
        if($query->num_rows() == 0)
        {
            $this->db->insert("users", ["email"=>$email, "session"=>session_id()]);
        }else{
            $this->db->update("users", ["session" =>session_id()], ["email"=>$email]);
        }
        echo "{}";
    }
    
    public function getinfo()
    {
        header("Content-Type: application/json", true);
        $query = $this->db->get_where('users', ['session' => session_id()]);
        if($query->num_rows() > 0)
        {
            echo json_encode($query->row());
        }
    }
    
   
    
    
    public function charging()
    {
        //header("Content-Type: application/json", true);
        $this->db->where("session", session_id());
        $query= $this->db->get("users");
        if($query->num_rows()==0){ echo "Session not found"; return;};
        $data = $query->row();
        
        $coint = (int)$data->coint;
        
        $payment = new Vtcpayment();
        //$raw = '{"Balance": "100000", "Description":"Nạp thành công 100k"}';
        $raw = $payment->charging();
        
        $rs = json_decode($raw);
        if($rs->Extend != NULL)
        {
            $add = (int) explode('|', $rs->Extend)[0];
            $query= $this->db->update("users", ['coint' => ($coint  + $add)], ["session" => session_id()]);
        };
        echo $raw;
       
    }
    
    public function buy()
    {
        $ids = $_POST['ids'];
        if(!isset($ids) || count($ids)==0){die("Lỗi"); };
        foreach($ids as $id)
        {
            $course = $this->db->get_where('courses', ['id'=>$id])->row();
            $user   = $this->db->get_where('users', ['session'=>session_id()])->row();
            if($user->coint >= $course->price)
            {
                $bought = $user->bought==''?$course->id:$user->bought.','.$course->id;
                $this->db->update(
                'users', 
                ['coint'=> ($user->coint - $course->price), 'bought'=> $bought],
                ['session'=>session_id()]
                );
               
               $email = $user->email;
               $group = $course->email;
               $addMember = new UrlFetch((object)['url' => "https://script.google.com/macros/s/AKfycbzid65t2QIlqDSiAUSCvxkMbWiDBLH9vq_zz8_tBboACo9oHZ-m/exec?email=$email&group=$group"]);
               $addMember->fetch();
                
            }else{echo "Thiếu tiền"; return;};
            echo "Mua khóa học thành công, mời bạn check Gmail";
            
        }
        // Fetch remote serrverr
    }
    
    public function getcaptcha()
    {
         $captcha = new Vtcpayment();
         echo $captcha->getCaptcha();
         
    }
    
    public function getCoursesList()
    {
        $rs = $this->db->get('courses')->result();
        echo json_encode($rs);
    }
}
?>