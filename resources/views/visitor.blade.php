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
            .visits{
                position:relative;
                width:100%;
            }
            .visits-head{
                font-weight: bold;
                border-bottom: 1px solid #aa2aee;
            }
            .visits-title{
                margin-top:60px;
                font-weight: bold;
                font-size:1.1em;
            }
        </style>
    </head>
    <body>
        <div class="wrapper">
            <ul class="visitor-info">
                <li>Уникальный идентификатор: {{$visitor->hash}}</li>
                <li>Дата первого посещения: {{$visitor->created_at}}</li>
            </ul>

            <div class="visitor-keywords">
                <div class="visitor-keywords__title">Интересы пользователя</div>
                <ul>

                    @foreach($visitorKeywords as $visitorKeyword=>$visitorKeywordWeight)
                        <li>{{$visitorKeyword}}: {{$visitorKeywordWeight}}</li>
                    @endforeach
                </ul>
            </div>

            <div class="visits-title">Посещения сайтов пользователем:</div>
            <table class="visits">
                 <tr class="visits-head visit-row">
                     <th>URL</th>
                     <th>Время</th>
                     <th style="width:10%">Время пребывания(сек.)</th>
                     <th>IP</th>
                     <th>User Agent</th>
                     <th>Ключевые слова</th>
                 </tr>

                 @foreach($visits as $visit)
                     <tr class="visit-row">
                         <td><a href="/links/{{$visit->site_link->id}}">{{$visit->site_link->url}}</td>
                         <td>{{$visit->created_at}}</td>
                         <td>{{$visit->time_on_page}}</td>
                         <td>{{$visit->ip}}</td>
                         <td title="{{$visit->user_agent}}">{{substr($visit->user_agent,0,30)."..."}}</td>
                         <td>
                             @foreach($keywords[$visit->site_link->id] as $keyword )
                                 {{$keyword['name']." "}}
                             @endforeach
                         </td>
                     </tr>
                 @endforeach
            </table>
        </div>
        <script src="{{asset('js/app.js')}}"></script>

    </body>
</html>
