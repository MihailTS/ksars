<!doctype html>
<html>
    <head>
    </head>
    <body>
        @foreach($sites as $site)
            <div>
                <div>{{$site->url}}</div>
            </div>
        @endforeach
    </body>
</html>
