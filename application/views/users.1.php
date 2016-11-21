<!DOCTYPE HTML>

<html>

<head>
	<script type="text/javascript" src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
	<script src="https://apis.google.com/js/platform.js" async defer></script>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="google-signin-client_id" content="111103217218-mg1i8lkitggc208ustimejp3nbelccj9.apps.googleusercontent.com"> 
	<title>Nạp thẻ</title>
	<link rel="stylesheet" href="/css/style.css" type="text/css" />
	<script src="/js/library.js"></script>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <div id="login">
        <center>
    <span class="notice">Bấm nút dưới để đăng nhập với tài khoản Google mà bạn muốn dùng để học</span><br/><br/>
      <div class="g-signin2" data-onsuccess="onSignIn"></div>
      </center>
    </div>
    
    <div id="content">
    
    
        
    <div class="block">
        <h2>Thông tin tài khoản</h2>
        <table id="users-info">
            <tr><td>Email</td><td><span id="email"></span></td></tr>
            <tr><td>Tên</td><td><span id="name"></span></td></tr>
            <tr><td>Tài khoản</td><td><span id="coint"></span></td></tr>
        </table>
        <a href="javascript: signOut()">Đăng xuất</a>
    </div>
    
    <div class="block">
        <div id="bought">
        <h2>Các khóa đã mua</h2>
        <div></div>
        </div>
        
        <div id="notBought">
        <h2>Các khóa chưa mua</h2>
        <div>
        <form id="not-bought-form">
            <div></div>
        <input type="submit" value="Mua thêm" />
        </form>
        </div>
        </div>
    </div>
    
    <div class="block">
        <h2>Nạp tiền vào hệ thống</h2>
        <form id="charging_info">
        <table>
            <tr><td>Mã thẻ</td><td><input type="number" name="code" placeholder="Nhập mã thẻ" required /></td></tr>
            <tr><td>Số seri</td><td><input type="number"  name="serial" placeholder="Nhập số sê ri" required/></td></tr>
            <tr><td>Nhà mạng</td><td>
                <select name="type" required>
                    <option disabled selected>--</option>
                    <option value="1">Viettel</option>
                    <option value="2">Vinaphone</option>
                    <option value="3">Mobifone</option>
                    <option value="4">Vietnamobile</option>
                    <option value="5">Vcoin</option>
                </select>
            </td></tr>
        <table>
        <h3>Mã bảo mật</h3>
        <img id="captchaImg"/><br/>
        <input type="text" id="captcha" name="captcha" required/><br/>
        <button id="button">...</button>
        <input id="token" name="token" type="hidden"/>
        </form>
    </div>
    
    
    </div>  
    
    <script type="text/javascript">
    
        var courses;
 
        function signOut()
        {
            var auth2 = gapi.auth2.getAuthInstance();
             auth2.signOut().then(function () { console.log('User signed out.'); });
             $("#content").hide();
             $("#login").show();
             
        }
        
        
        function onSignIn(googleUser)
        {
            alert("Signed in");
            $("#content").show();
             $("#login").hide();
            var profile = googleUser.getBasicProfile();
            $("#email").html(profile.getEmail());
            $("#name").html(profile.getName());
            $.post("/users/loginas", {  email: profile.getEmail()},
            function(){getInfo();});
            
        }
      
        
        
        function getCaptcha()
        {
           $("#charging_info input").val('');
           $("#button").off().html("Đang lấy captcha");
           
           $.get("/users/getcaptcha", function(data, status){
               
             $("#button").html("Nạp thẻ ngay")
             .click(function(e){e.preventDefault(); charging(); });
             data = JSON.parse(data);
             $('#token').val(data.Verify);
             $('#captchaImg').attr('src', 'data:image/jpeg;base64,' + data.ImageData);
             
           }); 
           
        }
        
        
        function buy()
        {
            var list = [];
            var checked = $("#not-bought-form input[type=checkbox]:checked");
            if(checked.length == 0){ alert("Mời bạn chọn ít nhất 1 khóa"); return;};
            for(var el of checked){ list.push($(el).val()); };
            var data = "id="+list.join(",");
            $.post("/users/buy", data , function(dataa) {    alert(dataa);  getInfo(); });
            
        }
        
        function charging()
        {
            if($("#charging_info input[name=code]").val() == "")
            {
                alert("Nhập mã thẻ"); return;
            };
            if($("#charging_info input[name=serial]").val() == "")
            {
                alert("Nhập số seri"); return;
            };
            if($("#charging_info input[name=captcha]").val() == "")
            {
                alert("Nhập mã xác thực"); return;
            };
            if($("#charging_info option:checked").text() == "--")
            {
                alert("Chọn nhà mạng"); return;
            };
            $("#button").html("Đang nạp thẻ");
            var data = $("#charging_info").serialize();
            
            $.post("/users/charging", data,
            function(data)
            {
                alert(data);
                var data = JSON.parse(data);
                if(data.Balance > 0)
                { alert("Nạp thành công "+data.Balance);
                }else{
                    alert(data.Description);
                }
                getCaptcha();
                getInfo();
            });
        }
        
        function getInfo()
        {
            
            $("#notBought div").html("");
            $("#notBought div div ").html("");  
            $.get("/users/getinfo", function(data)
            {
                data = JSON.parse(data);
                $("#coint").html(data.coint+" đ");
                var listBought = data.bought.split(",");
                var courseInfo = function(listBought)
                {
                    
                    var html = new Object();
                    html.bought = 'Bạn bấm vào tên khóa học để xem chi tiết<ul>';
                    html.allBought = true;
                    html.allNotBought = true;
                    html.notBought = '';
                
                    for(var course of courses)
                    {
                        var match = false;
                        for(var damua of listBought)
                        {
                            if(parseInt(damua) == parseInt(course.id)){ match = true; };
                            break;
                        }
                        
                        if(match)
                        {
                            html.bought += '<li><a class="group_link" href="'+course.link+'">' + course.name + '</a></li>';
                            html.allNotBought = false;
                        }else{
                            html.notBought += '<input name="buy[]" type="checkbox" value="' + course.id + '"/>'+course.name+'<br/>';
                            html.allBought = false;
                        }
                        
                     };
                        html.bought += '</ul>';
                     
                     if(html.allNotBought){html.bought = "Bạn chưa mua khóa nào"; };
                     $("#bought div").html(html.bought);
                        
                     if(html.allBought )
                        {
                            $("#notBought div").html("Bạn đã mua hết các khóa");
                            
                        }else{
                            $("#notBought div div ").html(html.notBought);    
                        }
                        
                }
                
                if(courses == undefined)
                {
                    $.get("/courses/getCoursesList",
                    function(data) { courses=JSON.parse(data);  courseInfo(listBought); });     
                }else{
                     courseInfo(listBought);
                }
            }); 
            
            
        }
        
        
        
        (function init()
        {
            // Load info
            getCaptcha();
            
            //Bind event
            $("#not-bought-form").submit(function(e){  e.preventDefault(); buy();     });
            
            $("#content").hide();
            $("#login").show();
        })();
        
        
        
        
    </script>
  
</body>
