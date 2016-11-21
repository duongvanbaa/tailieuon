var GoogleAuth;
class User
{
    constructor(loginCallBack, logoutCallBack)
    {
        gapi.load('auth2', function()
        {
            GoogleAuth = gapi.auth2.init({
            client_id: '111103217218-mg1i8lkitggc208ustimejp3nbelccj9.apps.googleusercontent.com',
            cookiepolicy: 'single_host_origin'
        });
      
      var listener = function()
      {
        if(GoogleAuth.isSignedIn.get())
        {
            
            var GoogleUser = GoogleAuth.currentUser.get();
            $.ajax({
                url: '/users/loginas',
                method: 'post',
                async: false,
                data: {id: GoogleUser.getAuthResponse().id_token, email: GoogleUser.getBasicProfile().getEmail()}
            });
            
            
            $.ajax({
                url: '/users/getinfo',
                dataType: 'json',
                success: loginCallBack,
                async: false
            });
            
            
        }else{
            logoutCallBack();
        };
      };
      
      GoogleAuth.then(function(){ listener(); GoogleAuth.currentUser.listen( listener )});
      
    });
    }
    
    isLogged()
    {
        return GoogleAuth.isSignedIn.get();
    }
    
    
    login()
    {
      if(this.isLogged()){ this.logout(); };
      GoogleAuth.signIn();
      
    }
    
    logout()
    {
      GoogleAuth.disconnect();
      GoogleAuth.signOut(); 
    }

    
    buy(callback, list)
    {
        $.ajax({
                    url : '/users/buy',
                    method: 'post',
                    data: {ids: list},
                    async: 'false',
                    success: callback
                })
    }
    
    charging(callback, data)
    {
            $.ajax(
                {
                    url : '/users/charging',
                    method: 'post',
                    dataType: 'json',
                    async: 'false',
                    data: data,
                    success: function(data){ callback(data); }
                    
                }
                )
            
    }
    
    getInfo(callBack)
    {
        $.ajax({
                url: '/users/getinfo',
                dataType: 'json',
                success: callBack,
                async: false
            })
      
    }
}


class Utility
{
    getCaptcha(callback)
    {
        $.ajax({
            url: '/users/getcaptcha',
            dataType: 'json',
            success: callback,
            async: false
        })
    }
    
    
    getCoursesList()
    {
        var rsdata;
         $.ajax({
        url: '/courses/getcourseslist',
        dataType: 'json',
        async: false,
        success: function(data){  rsdata = data; }
        });
        return rsdata;
    }
}

class Show
{
    constructor(list)
    {
        this.list = list;
    }
    
    show(els)
    {
        if(typeof els === 'string'){ els = [els]; };
        for(var el of els)
        {
            for(var obj of this.list)
            {
                if(obj == el){ $(obj).show(); }else{ $(obj).hide(); };
            }
        }
    }
    
    
}

class Tip
{
    constructor(id)
    {
        this.id = id;
    }
    
    show(html, time, after)
    {
        var id = this.id;
        $(id).html(html);
        if(!after){ after = ''; };
        if(time)
        {
            setTimeout(function(){ $(id).html(after);}, time);
        }
    }
}