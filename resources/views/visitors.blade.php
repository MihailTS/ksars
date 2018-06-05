<!doctype html>
<html>
    <head>
        <link href="{{asset('css/app.css')}}" rel="stylesheet" type="text/css">
    </head>
    <body>
        <div>
            <div>
                <div>
                    <div>Уникальный идентификатор посетителя</div>
                    <div>Дата первого посещения</div>
                </div>
            </div>

            <div>
                @foreach($visitors as $visitor )
                    <div>
                        <div>{{$visitor->hash}}</div>
                        <div>{{$visitor->created_at}}</div>
                    </div>
                @endforeach
            </div>
        </div>
        <script src="{{asset('js/app.js')}}"></script>
    </body>
</html>
