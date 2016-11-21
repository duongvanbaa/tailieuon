<html>
<head>
  <title>Hệ thống nạp thẻ</title>
  
  <link rel="stylesheet" type="text/css" href="/css/style.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
  <script src="/js/library.js"></script>
  <script src="https://apis.google.com/js/api:client.js"></script>

</head>
<body>
  <div id="loader"><img src="gears.gif"/> </div>


  
  <div id="login">
    <span>Bấm vào đây để đăng nhập với tài khoản Google mà bạn muốn dùng để học</span>
    <br/>
    <img src="https://goo.gl/FVaMah"/>
    
  </div>
  
  <div id="content">
    
    
        
    <div class="block">
        <h2>Thông tin tài khoản</h2>
        <table id="users-info">
            <tr><td>Email</td><td><span id="email"></span></td></tr>
            <tr><td>Tên</td><td><span id="name"></span></td></tr>
            <tr><td>Tài khoản</td><td><span id="coint"></span></td></tr>
        </table>
        <div id="logout" class="button">
        <img src="https://goo.gl/5qG3gR" class="icon" /> Đăng xuất khỏi tài khoản
        </div>
    </div>
    
    <div class="block">
        <div id="boughtInfo">
        <img src="https://i.imgsafe.org/09d7269a29.png" class="icon"/><span></span>
        </div>
        
        <div id="bought">
          <h2>Các khóa đã mua</h2>
          <div></div>
        </div>
        
        
        <div id="notBought">
          <h2>Các khóa chưa mua</h2>
          <form id="buy-form">
          <div></div>
          <button class="button"><img src="http://image.flaticon.com/icons/svg/61/61112.svg" class="icon"/>Mua thêm</button>
          </form>
        </div>
        
      
        
    </div>
    
    <div class="block" id="charging_block">
        <h2>Nạp tiền vào hệ thống</h2>
        <span></span>
        <form id="charging_info">
        <table>
            <tr><td>Mã thẻ</td><td><input type="number" name="code" placeholder="Nhập mã thẻ" class="input" required /></td></tr>
            <tr><td>Số seri</td><td><input type="number"  name="serial" placeholder="Nhập số sê ri" class="input"  required/></td></tr>
            <tr><td>Nhà mạng</td><td>
                <select name="type" required>
                    <option disabled selected>--</option>
                    <option value="1">Viettel</option>
                    <option value="2">Vinaphone</option>
                    <option value="3">Mobifone</option>
                    <option value="4">Vietnamobile</option>
                    <option value="5">Vcoin</option>
                </select>
            </td>
            </tr>
            <tr><td>Mã bảo mật</td><td><img id="imgCaptcha"/><img id="reLoad" src="https://goo.gl/ium8uo" title="Đổi mã"/></td></tr>
            <tr><td>Nhập mã bảo mật</td><td><input type="text" id="captcha" name="captcha" required/></td></tr>
        <table>
        <button id="button" class="button"><img src="https://goo.gl/ecqC1Y" class="icon"/> Nạp tiền vào tài khoản</button>
        <input id="token" name="token" type="hidden"/>
        </form>
    </div>
    
    
    </div>  
<script>

function getCaptcha(captcha)
{
  $('#charging_info input[name="serial"]').val('');
  $('#charging_info input[name="code"]').val('');
  $("#captcha").val('');
  $('#token').val(captcha.Verify);
  $('#imgCaptcha').attr('src', 'data:image/jpeg;base64,' + captcha.ImageData);
}

function showInfo(courses, info)
{
  // Cacaulate
    var notBought = [];
    var bought    = [];
    info.bought = info.bought.split(",");
    
    for(var i = 0; i < courses.length; i++)
    {
      var match = false;
      for(var j=0; j<info.bought.length; j++)
      {
        if( courses[i].id == info.bought[j]){ match =true; break;};
      }
      if(match){ bought.push(courses[i]); }else{ notBought.push(courses[i]); };
    };
    
    
    // Hiển thị
    if(bought.length == courses.length)
    {
      var notice = `Bạn đã mua hết ${courses.length} khóa học của page`;
      }else{
        if(bought.length==0)
        {
          var notice = `Bạn chưa mua khóa nào trong ${courses.length} khóa học có trên hệ thống`;
        }else{
        var notice = `Hệ thống có ${courses.length} khóa học, bạn đã mua ${bought.length} khóa`;
      }
    };
    
    $("#boughtInfo span").html(notice);
    
    // Các khóa đã mua
    if(bought.length > 0) // Nếu chưa mua khóa nào thì hiện "Toàn bộ chưa mua"
    {
      $("#bought").show();
      let html = '<ul class="coursesList">';
      var open = function(url){ window.open(url, '_blank').focus(); };
      for(let course of bought)
      {
        html += `
        <li onclick="(${open})('${course.link}');">
        <img src="https://i.imgsafe.org/0a5b36f74e.png" class="icon"/>
        <span class="title">${course.name}</span><br/>
        <span class="describle">${course.describle}</span>
        </li>  
        `;
      }
      html += '</ul>';
      $("#bought div").html(html);
    }else{
      $("#bought").hide();
    }
    
    // Các khóa chưa mua
    if(notBought.length > 0) // Nếu toàn bộ đã mua
    {
      $("#notBought").show();
      let html = '<ul class="coursesList">';
      for(let course of notBought)
      {
        html += `
        <li>
        <input name="list[]" type="checkbox" value="${course.id}"/>
        <span class="title">${course.name}</span><br/>
        <span class="price">Giá tiền: ${course.price} đ</span><br/>
        <span class="describle">${course.describle}</span>
        </li>  
        `;
      }
      html += '</ul>';
      $("#notBought div").html(html);
      $("#notBought li").click(function(){
        var el = $("input", this);
        var stt = el.prop('checked');
        el.prop('checked', !stt);
        if(!stt){ $(this).prop('class','selected'); }else{$(this).prop('class',''); };
      });
    }else{
      $("#notBought").hide();
    }
    
    // In thông tin tài khoản
    $("#coint").html(info.coint+" đ");
    $("#email").html(info.email);
    var name = GoogleAuth.currentUser.get().getBasicProfile().getName();
    $("#name").html(name);
}

  

(function init()
{
  
  // Tạo các biến tiện tích, điều khiền view, lấy data
  var utility = new Utility();
  
  utility.getCaptcha(getCaptcha);
  var courses = utility.getCoursesList();
    
    
  var block = new Show(['#content','#login']);
  
  
  // Bắt sự kiện đăng nhập, đăng xuất
  var onLogin = function(info){
    $("#loader").show();
    var wait = function()
    {
      block.show('#content');  showInfo(courses, info); $("#loader").hide(); $('html,body').scrollTop(0);
    };
    setTimeout(wait, 2000);
    
  }
  var onLogout = function() {  block.show('#login'); $("#loader").hide(); $('html,body').scrollTop(0);  }
  var user  = new User(onLogin, onLogout);
  
  
  
  
  // Nút đăng nhập, đăng xuất
  
  $("#login") .click(function(){ user.login() ;});
  $("#logout").click(function(){
    $("#loader").show();
    setTimeout(function(){ user.logout();}, 2000);
    
  });
  
  // Nút mua thêm
  $("#buy-form").submit(function(e)
  {
    e.preventDefault();
    
    var list = [];
    var data = $("#buy-form input[type=checkbox]:checked");
    if(data.length==0){ alert("Bạn vui lòng chọn mua ít nhất 1 khóa học"); return; };
    $("#loader").show();
    var next = function(){
    for(var el of data){ list.push($(el).val()); };
    
    // Gửi lệnh mua
    user.buy(function(tip){
      // Sau khi mua xong thì lấy lại thông tin người dùng
      user.getInfo(function(data){
          alert(tip); // In kết quả mua
          // Hiện thông tin đã lấy được và tắt loader
          showInfo(courses, data); 
          $("#loader").hide();
          $('html,body').scrollTop(0);
      });
    }, list);
    
    };
    
    setTimeout(next, 1000);
  })
 
 
  $("#charging_info").submit(function(e)
  {
    e.preventDefault();
    if($("#charging_info option:selected").text() == '--'){ alert("Vui lòng chọn nhà mạng"); return; };
    var tip = new Tip("#charging_block span");
    tip.show('Đang nạp thẻ...');
   
    var data = $(this).serialize();
    
    // Nếu gửi lệnh nạp thẻ thành công
    var callback = function(info)
    {
      
      // Lấy thông tin người dùng
      user.getInfo(function(data){
          // Hiện kết quả nạp thẻ
          if(info.Extend != null){ info.Description += ' '+info.Extend.split('|')[0]+' đ'; };
          tip.show(info.Description, 5000);
          // Update thông tin người dùng
          showInfo(courses, data);
        
      });
      
    };
    
    // Nạp thẻ
    user.charging(callback, data);
    
    utility.getCaptcha(getCaptcha);
  });
  
  $("#captcha").keyup(function(){
    $(this).val($(this).val().toUpperCase());
  });
  
  $("#reLoad").click(function(){
    var that = this;
    $(this).css({animation: 'rotating 0.5s linear infinite'});
    
    utility.getCaptcha(function(captcha){
      setTimeout(function(){$(that).css({animation: ''});}, 1000) 
      getCaptcha(captcha);
    });
  });
    
    
}
)();

</script>
  
  
  
</body>
</html>