<!doctype html>
<html>
    <head>
        <link href="{{asset('css/app.css')}}" rel="stylesheet" type="text/css">

        <style>
            body{
                font-family: sans-serif;
            }
            .visitors{
                display:flex;
                flex-direction: column;
                width:80%;
                padding:30px 10%;
            }
            .visitors-head{
                font-weight: bold;
                border-bottom: 1px solid #aa2aee;
            }
            .visitor-row{
                display: flex;
            }
            .visitor-row div{
                flex:1 1;
                margin:0 10px;
            }
        </style>
    </head>
    <body>
        <div class="visitors">
            <div class="visitors-head">
                <div class="visitor-row">
                    <div>Уникальный идентификатор посетителя</div>
                    <div>Дата первого посещения</div>
                </div>
            </div>

            <div class="visitors-list">
                @foreach($visitors as $visitor )
                    <div class="visitor-row">
                        <div><a href="/visitors/{{$visitor->id}}">{{$visitor->hash}}</a></div>
                        <div>{{$visitor->created_at}}</div>
                    </div>
                @endforeach
            </div>
        </div>
        <script src="{{asset('js/app.js')}}"></script>
    </body>
</html>
