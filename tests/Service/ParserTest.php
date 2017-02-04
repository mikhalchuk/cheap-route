<?php

namespace CheapRoute\Tests\Service;

use PHPUnit\Framework\TestCase;

use CheapRoute\Service\Parser;

class ParserTest extends TestCase
{
    public function getHtml()
    {
        return file_get_contents(
            __DIR__ . '/../data/flypgs_response.html'
        );
    }

    public function testDepartures()
    {
        $departures = Parser::parse(
            $this->getHtml(),
            Parser::DEPARTURE_CLASSES
        );

        $this->assertCount(3, $departures);

        $expectedDepartures = [
            [
                'departureDate' => new \DateTime('2017-02-24 00:20:00'),
                'arrivalDate' => new \DateTime('2017-02-24 01:30:00'),
                'price' => [
                    'amount' => '17.00',
                    'currency' => 'EUR',
                ],
            ],
            [
                'departureDate' => new \DateTime('2017-02-25 21:00:00'),
                'arrivalDate' => new \DateTime('2017-02-25 22:00:00'),
                'price' => [
                    'amount' => '21.00',
                    'currency' => 'EUR',
                ],
            ],
            [
                'departureDate' => new \DateTime('2017-02-26 00:20:00'),
                'arrivalDate' => new \DateTime('2017-02-26 01:30:00'),
                'price' => [
                    'amount' => '14.00',
                    'currency' => 'EUR',
                ],
            ],
        ];

        $this->assertEquals($expectedDepartures, $departures);
    }

    public function testReturns()
    {
        $returns = Parser::parse(
            $this->getHtml(),
            Parser::RETURN_CLASSES
        );

        $this->assertCount(3, $returns);

        $expectedReturns = [
            [
                'departureDate' => new \DateTime('2017-02-27T08:10:00'),
                'arrivalDate' => new \DateTime('2017-02-27T09:15:00'),
                'price' => [
                    'amount' => '17.00',
                    'currency' => 'EUR',
                ],
            ],
            [
                'departureDate' => new \DateTime('2017-02-28T08:10:00'),
                'arrivalDate' => new \DateTime('2017-02-28T09:15:00'),
                'price' => [
                    'amount' => '14.00',
                    'currency' => 'EUR',
                ],
            ],
            [
                'departureDate' => new \DateTime('2017-03-01T08:10:00'),
                'arrivalDate' => new \DateTime('2017-03-01T09:15:00'),
                'price' => [
                    'amount' => '14.00',
                    'currency' => 'EUR',
                ],
            ],
        ];

        $this->assertEquals($expectedReturns, $returns);
    }
}
