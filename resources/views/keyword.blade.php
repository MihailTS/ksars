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
            .keyword-head{
                font-weight: bold;
                border-bottom: 1px solid #aa2aee;
            }
            .keyword-site-links-title{
                margin-top:60px;
                font-weight: bold;
                font-size:1.1em;
            }
            .keyword-name{
                font-size: 1.4em;
            }
        </style>
    </head>
    <body>
        <div class="wrapper">
            <h1 class="keyword-head">Ключевое слово: <b class="keyword-name">{{$keywordName}}</b></h1>

            <div class="keyword-site-links-title">Страницы с этим ключевым словом:</div>
            <ul class="similar">
                @foreach($siteLinksWithKeyword as $siteLink)
                    <li class="keyword-site-link-row">
                        <a href="/links/{{$siteLink->id}}">{{$siteLink->url}} ({{$siteLink->coefficient}})</a>
                    </li>
                @endforeach
            </ul>
        </div>
        <script src="{{asset('js/app.js')}}"></script>

    </body>
</html>
