<?php

namespace Morrison\Web\Helper\Tests;

use Morrison\Web\Helper\VoteChecker;
use Monolog\Logger;

class VoteCheckerTest extends \PHPUnit_Framework_TestCase
{

    public function testIpAllowed()
    {
        $logger = $this->getMockBuilder(Logger::class)->disableOriginalConstructor()->getMock();
        $redis  = $this->getMockBuilder(MockRedis::class)->getMock();
        $voteChecker = new VoteChecker($logger, $redis);
        $requestLimitRate = $voteChecker->getRequestRateLimit();
        $currentRequestTimes = 5;
        $redis->expects($this->any())->method('incr')->will($this->returnValue($currentRequestTimes));
        $this->assertEquals($requestLimitRate >= $currentRequestTimes, $voteChecker->ipAllowed('192.168.1.11'));
    }
}
