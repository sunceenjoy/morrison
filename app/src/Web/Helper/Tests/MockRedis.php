<?php

namespace Morrison\Web\Helper\Tests;

use Predis\Client;

class MockRedis extends Client
{
    public function incr()
    {
        // We can not mote Predis\Client directly, because of:
        // Trying to configure method "incr" which cannot be configured because it does not exist, has not been specified, is final, or is static
    }
}
