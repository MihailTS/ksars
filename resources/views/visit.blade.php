<!doctype html>
<html>
    <head>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link href="{{asset('css/app.css')}}" rel="stylesheet" type="text/css">
    </head>
    <body>
        <div id="app"></div>
        <script>
            function Ksars() {};
            Ksars.prototype = {
                visitorID: null,
                visitID: null,
                getCookie: function(name) {
                    var matches = document.cookie.match(new RegExp(
                        "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
                    ));
                    return matches ? decodeURIComponent(matches[1]) : undefined;
                },
                setCookie: function (name, value, options) {
                    options = options || {};

                    var expires = options.expires;

                    if (typeof expires == "number" && expires) {
                        var d = new Date();
                        d.setTime(d.getTime() + expires * 1000);
                        expires = options.expires = d;
                    }
                    if (expires && expires.toUTCString) {
                        options.expires = expires.toUTCString();
                    }

                    value = encodeURIComponent(value);

                    var updatedCookie = name + "=" + value;

                    for (var propName in options) {
                        updatedCookie += "; " + propName;
                        var propValue = options[propName];
                        if (propValue !== true) {
                            updatedCookie += "=" + propValue;
                        }
                    }
                    document.cookie = updatedCookie;
                },
                identifyUser: function(){
                    var xhr = new XMLHttpRequest();
                    var params = "";
                    var ksarsCookie = this.getCookie("ksars");
                    if(ksarsCookie){
                        this.visitorID = ksarsCookie;
                        params += "ksars="+ ksarsCookie;
                    }

                    xhr.open('POST', 'http://ksars/testreceiver/');
                    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                    var that = this;
                    xhr.onreadystatechange=function(){
                        if (xhr.readyState !== 4) return;
                        if (xhr.status === 200) {
                            var response = JSON.parse(xhr.responseText);
                            that.visitorID = response.visitor;
                            that.visitID = response.visit;
                            that.setCookie("ksars",response.visitor,{expires:60*60*24*30});
                        }
                        setInterval(function(){that.sendVisitTimeData()},2000);
                    };
                    xhr.send(params);

                },
                sendVisitTimeData: function(){
                    var xhr = new XMLHttpRequest();
                    var params = "visitor=" + this.visitorID + "&visit=" + this.visitID;
                    xhr.open('POST', 'http://ksars/testvisittime/');
                    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                    xhr.send(params);
                }
            };
            var ksars = new Ksars();
            ksars.identifyUser();
        </script>
    </body>
</html>
