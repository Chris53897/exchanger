<?php

/*
 * This file is part of Exchanger.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Exchanger\Tests\Service;

use Exchanger\HistoricalExchangeRateQuery;
use Exchanger\CurrencyPair;
use Exchanger\ExchangeRateQuery;
use Exchanger\Service\Google;

class GoogleTest extends ServiceTestCase
{
    /**
     * @test
     */
    public function it_does_not_support_all_queries()
    {
        $service = new Google($this->getMock('Http\Client\HttpClient'));

        $this->assertTrue($service->supportQuery(new ExchangeRateQuery(CurrencyPair::createFromString('USD/EUR'))));
        $this->assertFalse($service->supportQuery(new HistoricalExchangeRateQuery(CurrencyPair::createFromString('EUR/USD'), new \DateTime())));
    }

    /**
     * @test
     * @expectedException \Exchanger\Exception\Exception
     */
    public function it_throws_an_exception_when_rate_not_supported()
    {
        $uri = 'http://finance.google.com/finance/converter?a=1&from=EUR&to=XXL';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Service/GoogleFinance/unsupported.html');

        $service = new Google($this->getHttpAdapterMock($uri, $content));
        $service->getExchangeRate(new ExchangeRateQuery(CurrencyPair::createFromString('EUR/XXL')));
    }

    /**
     * @test
     */
    public function it_fetches_a_rate()
    {
        $url = 'http://finance.google.com/finance/converter?a=1&from=EUR&to=USD';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Service/GoogleFinance/success.html');

        $service = new Google($this->getHttpAdapterMock($url, $content));
        $rate = $service->getExchangeRate(new ExchangeRateQuery(CurrencyPair::createFromString('EUR/USD')));

        $this->assertSame('1.1825', $rate->getValue());
        $this->assertInstanceOf('\DateTime', $rate->getDate());
    }
}
