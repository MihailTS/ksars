<!doctype html>
<html>
    <head>
        <link href="{{asset('css/app.css')}}" rel="stylesheet" type="text/css">
    </head>
    <body>
        @foreach($sites as $site)
            <div>
                <div>{{$site->url}}</div>
            </div>
        @endforeach
        <script src="{{asset('js/app.js')}}"></script>
    </body>
</html>
