<?php

namespace tests\AppBundle\Twig;

use AppBundle\Twig\DateExtension;

class TestDateExtension extends \PHPUnit_Framework_TestCase
{
    public function testGetStyle() // testujemy zwracany styl w zależności od daty
    {
        $dateExtension = new DateExtension();

        $this->assertEquals("panel-default", $dateExtension->auctionStyle(new \DateTime("+2 days")));
                        //wartość oczekiwana, Wartość rzeczywista 
        $this->assertEquals("panel-danger", $dateExtension->auctionStyle(new \DateTime("+20 minutes")));
    }
}
