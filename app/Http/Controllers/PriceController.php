<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Currency;
use App\Price;

class PriceController extends Controller
{
    public function show()
    {
        $this->update();

        $priceList =
            Currency::with('latestPrice')
                ->get()
                ->sortByDesc('latestPrice.change24');

        $lastFetch =
            Price::select('created_at')
            ->orderBy('created_at', 'desc')
            ->first()->created_at;

        return view('index', [
            'priceList' => $priceList,
            'lastFetch' => $lastFetch
        ]);
    }

    public function fetchUpdate(Request $request, $createdAt)
    {
        $this->update();

        return
            Currency::with('latestPrice')
                ->get()
                ->where('latestPrice.created_at', '>', $createdAt)
                ->sortByDesc('latestPrice.change24');
    }

    private function update()
    {
        $currenciesList = $this->getCurrenciesList();
        $file = $this->getPage();
        $currenciesDOM = $this->getCurrenciesDOM($file);
        $this->parse($currenciesDOM, $currenciesList);
    }

    private function getPage()
    {
        $url = 'https://coinmarketcap.com/all/views/all/';
        $options = array(
            'http' => array(
                'method' => "GET",
                'header' =>
                    "Accept-language: en\r\n"
                    . "User-Agent: "
                    . "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/534.24 "
                    . "(KHTML, like Gecko) Chrome/11.0.697.0 Safari/534.24\r\n"
            )
        );

        $context = stream_context_create($options);
        return file_get_contents($url, false, $context);
    }

    private function getCurrenciesDOM($file)
    {
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        @$dom->loadHTML($file);

        $currenciesDOM =
            $dom->getElementById('currencies-all')
                ->getElementsByTagName('tbody')[0]
                ->getElementsByTagName('tr');

        return $currenciesDOM;
    }

    private function getCurrenciesList()
    {
        $currencies = Currency::with('latestPrice')->get();
        $currenciesList = [];

        foreach ($currencies as $currency) {
            $currenciesList[$currency->name . $currency->symbol] = [
                'id' => $currency->id,
                'amount' => $currency->latestPrice['amount'],
                'change24' => $currency->latestPrice['change24'],
            ];
        }

        return $currenciesList;
    }

    private function parse($currenciesDOM, $currenciesList)
    {
        foreach ($currenciesDOM as $currencyDOM) {
            $name = trim($currencyDOM->childNodes[2]->textContent);
            $symbol = trim($currencyDOM->childNodes[4]->textContent);
            $uniqueKey = $name . $symbol;

            $amount =
                (float) str_replace('$', '',
                    trim(
                        $currencyDOM->childNodes[8]->textContent
                    )
                );

            $change24 = (float) $currencyDOM->childNodes[16]->textContent;

            $price = new Price();
            $price->amount = $amount;
            $price->change24 = $change24;

            if (!array_key_exists($uniqueKey, $currenciesList)) {
                $currency = new Currency();
                $currency->name = $name;
                $currency->symbol = $symbol;
                $currency->save();

                $price->currency_id = $currency->id;
                $price->save();
            }

            if (
                array_key_exists($uniqueKey, $currenciesList)
                && (
                    $amount !== $currenciesList[$uniqueKey]['amount']
                    || $change24 !== $currenciesList[$uniqueKey]['change24']
                )
            ) {
                $price->currency_id = $currenciesList[$uniqueKey]['id'];
                $price->save();
            }
        }
    }
}
