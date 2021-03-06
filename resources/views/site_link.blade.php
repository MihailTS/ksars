<!doctype html>
<html>
    <head>
        <link href="{{asset('css/app.css')}}" rel="stylesheet" type="text/css">

        <style>
            body{
                font-family: sans-serif;
            }
            .wrapper{
                padding:20px 1%;
            }
            .similar{
                position:relative;
                width:100%;
            }
            .similar-head{
                font-weight: bold;
                border-bottom: 1px solid #aa2aee;
            }
            .similar-title{
                margin-top:60px;
                font-weight: bold;
                font-size:1.1em;
            }
        </style>
    </head>
    <body>
        <div class="wrapper">
            <h1>Страница {{$siteLink->url}}</h1>
            <ul class="link-info">
                <li>Принадлежит к сайту {{$siteLink->site->url}}</li>
                <li>Данные о странице последний раз обновлялись: {{$siteLink->updated_at}}</li>
            </ul>
            @if($siteLinkKeywords)
            <div class="link-keywords">
                <div class="link-keywords__title">Ключевые слова страницы:</div>
                <ul>

                    @foreach($siteLinkKeywords as $linkKeyword)
                        <li><a href="/keywords/{{$linkKeyword['name']}}">{{$linkKeyword['name']}}: {{$linkKeyword['weight']}}</a></li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if($similarLinks)
            <div class="similar-title">Страницы похожие на эту:</div>
            <ul class="similar">

                @foreach($similarLinks as $similarLink)
                    <li class="visit-row">
                        <a href="/links/{{$similarLink['entity']->id}}">{{$similarLink['entity']->url}} ({{$similarLink['weight']}})</a>
                    </li>
                @endforeach
            </ul>
            @endif

        </div>
        <script src="{{asset('js/app.js')}}"></script>

    </body>
</html>
