<?php

namespace tests\AppBundle\Service;

use AppBundle\Service\DateService;

class TestDateService extends \PHPUnit_Framework_TestCase //do testów wykorzystujemy phpunit
{
    public function testGetDay()//sprawdzamy czy prawidłowo zwracany jest dzień z daty serwisu
    {
        $dateService = new DateService();

        $this->assertEquals(19, $dateService->getDay(new \DateTime("2017-01-19")), "Powinien być zwrócony dzień 19");
        $this->assertEquals(1, $dateService->getDay(new \DateTime("2018-01-01")));
    }
}
