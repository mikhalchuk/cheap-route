<?php

namespace CheapRoute\Service;

use Symfony\Component\DomCrawler\Crawler;

class Parser
{
    const DEPARTURE_CLASSES = [
        'prevDayFlightsDEP' => 'flightPrevDayContainerDEP',
        'actualDayFlightsDEP' => 'flightActualDayContainerDEP',
        'nextDayFlightsDEP' => 'flightNextDayContainerDEP',
    ];

    const RETURN_CLASSES = [
        'prevDayFlightsRET' => 'flightPrevDayContainerRET',
        'actualDayFlightsRET' => 'flightActualDayContainerRET',
        'nextDayFlightsRET' => 'flightNextDayContainerRET',
    ];

    public static function parse(string $html, array $config): array
    {
        $crawler = new Crawler($html);

        $res = [];

        foreach ($config as $header => $container) {
            $date = $crawler->filterXPath(
                sprintf('//*[@id="%s"]/div[1]/div', $header)
            )->html();

            $day = substr($date, 0, strrpos($date, ' '));

            /** @var \DOMElement $flightColumn */
            foreach ($crawler->filterXPath(
                sprintf('//*[@id="%s"]', $container)
            )->children() as $flightColumn) {

                try {
                    $colCrawler = new Crawler($flightColumn);

                    $departure = $colCrawler->filterXPath('//*/div[1]/div/span[1]')->html();
                    $departure = str_replace('<span>Departure</span>', '', $departure);

                    $arrival = $colCrawler->filterXPath('//*/div[1]/div/span[2]')->html();
                    $arrival = str_replace('<span>Arrival</span>', '', $arrival);

                    $price = trim($colCrawler->filterXPath('//*/div[2]/div/span[2]')->html());

                    $price = preg_replace('#<sup>(.*)</sup>#', '$1', $price);


                    $price = str_replace(chr(194), '', $price);
                    $price = str_replace(chr(160), ' ', $price);

                    //for ($i = 0; $i < strlen($price); $i++) {
                      //  echo $price[$i] . ': ' . ord($price[$i]) . PHP_EOL;
                    //}
                    //dump(explode(' ', $price));die;


                    list($amount, $currency) = explode(' ', $price);

                    $departureDate = \DateTime::createFromFormat(
                        'd F H:i', "{$day} {$departure}"
                    );

                    $arrivalDate = \DateTime::createFromFormat(
                        'd F H:i', "{$day} {$arrival}"
                    );

                    $res[] = [
                        'departureDate' => $departureDate,
                        'arrivalDate' => $arrivalDate,
                        'price' => [
                            'amount' => $amount,
                            'currency' => $currency,
                        ],
                    ];
                } catch (\InvalidArgumentException $e) {
                    // TODO: debug the problem
                    continue;
                }
            }
        }

        return $res;
    }
}
