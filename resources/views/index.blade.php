<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Crypto currencies price/changes</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Raleway', sans-serif;
                font-weight: 100;
                height: 100vh;
                margin: 0;
            }

            th {
                text-align: left;
            }
        </style>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

        <script type="text/javascript">
            var lastFetch = '{{ $lastFetch }}';

            $(document).ready(function() {
                $("#nameFilter").keyup(function () {
                    var rows = $("table").find("tbody > tr").hide();
                    if (this.value.length) {
                        rows.filter(":contains('" + this.value + "')").show();
                    } else {
                        rows.show();
                    }
                });

                var update = function() {
                    $.get('/fetchUpdate/' + encodeURIComponent(lastFetch), function(data) {
                        $.each( data, function( key, value ) {
                            var id = value.id,
                                name = value.name,
                                amount = value.latest_price.amount + ' $',
                                change24 = value.latest_price.change24 + ' %';

                            $('#amount-' + id).val(amount);
                            $('#change-' + id).val(change24);

                            lastFetch = value.latest_price.created_at;

                            $('<p>Update! Currency: ' + name + ' new price – '
                                + amount + '; change 24 – ' + change24 +
                              '</p>').appendTo('.status_bar');
                        });

                        update();
                    });
                };

                update();
            });
        </script>
    </head>
    <body>
        <h1>Crypto currencies price/changes</h1>
        <div class="status_bar"></div>
        <table>
            <thead>
            <tr>
                <th>Name</th>
                <th>Price</th>
                <th>% Change (24h)</th>
            </tr>
            <tr>
                <th>
                    <input id="nameFilter" value="" placeholder="Currency filter">
                </th>
                <th></th>
                <th></th>
            </tr>
            </thead>
            <tbody>
@foreach ($priceList as $price)
            <tr style="color:
                @if ($price->latestPrice['change24'] > 0)
                    green
                @elseif ($price->latestPrice['change24'] < 0)
                    red
                @endif"
            >
                <td>{{ $price->name }}</td>
                <td id="amount-{{ $price->id }}">{{ $price->latestPrice['amount'] }} $</td>
                <td id="change-{{ $price->id }}">{{ $price->latestPrice['change24'] }} %</td>
            </tr>
@endforeach
            </tbody>
        </table>
    </body>
</html>