<?php

namespace App\Http\Controllers;

use App\Currency;
use App\Price;

class PriceController extends Controller
{
    public function show()
    {
        $currenciesList = $this->getCurrenciesList();
        $file = $this->getPage();
        $currenciesDOM = $this->getCurrenciesDOM($file);
        $this->parse($currenciesDOM, $currenciesList);

        $priceList =
            Currency::with('latestPrice')
                ->get()
                ->sortByDesc('latestPrice.change24');

        return view('index', ['priceList' => $priceList]);
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
            $currenciesList[$currency->name] = [
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

            if (!array_key_exists($name, $currenciesList)) {
                $currency = new Currency();
                $currency->name = $name;
                $currency->save();

                $price->currency_id = $currency->id;
                $price->save();
            }

            if (
                array_key_exists($name, $currenciesList)
                && (
                    $amount !== $currenciesList[$name]['amount']
                    || $change24 !== $currenciesList[$name]['change24']
                )
            ) {
                $price->currency_id = $currenciesList[$name]['id'];
                $price->save();
            }
        }
    }
}
