<?php
namespace Morrison\Web\Helper;

use Monolog\Logger;
use Predis\Client;
use Predis\PredisException;

class VoteChecker
{
    /** @var Client $redis */
    private $redis;

    /** @var Logger $logger */
    private $logger;

    /** @var int $voteInterval
     * A same ip only can vote once in 10 seconds
     */
    private $voteInterval = 10;

    /**
     *  @var int $requestRateLimit
     *  In a short time($voteInterval * $requestRateLimit)
     *  a same ip can vote $requestRateLimit times
     */
    private $requestRateLimit = 3;
    
    /** @var string $keyPrefix */
    private $keyPrefix;
    
    public function __construct(Logger $logger, Client $redis, $keyPrefix = '')
    {
        $this->redis = $redis;
        $this->keyPrefix = $keyPrefix;
        $this->logger = $logger;
    }

    /**
     * Test whether the email addresss is voted
     * @param string $emailAddress
     * @return BOOL TRUE if value is a member of the set at key key, FALSE otherwise.
     */
    public function checkEmailAddress($emailAddress)
    {
        try {
            return $this->redis->sIsMember($this->keyPrefix.'_vote_list', $emailAddress);
        } catch (PredisException $e) {
            $this->logger->err('Redis Communication Issue', [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
            ]);
            return false;
        }
    }
    /**
     * Count a new  voted email
     * @param LONG the number of elements added to the set | false
     */
    public function newEmailAddress($emailAddress)
    {
        try {
            return $this->redis->sAdd($this->keyPrefix.'_vote_list', $emailAddress);
        } catch (PredisException $e) {
            $this->logger->err('Redis Communication Issue', [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
            ]);
            return false;
        }
    }
    
    /**
     * Check whether the ip is allowed to vote
     * @param BOOL true if the ip is allowed to vote, false otherwise.
     */
    public function ipAllowed($ipAddress)
    {
        $requestRateLimit = $this->requestRateLimit;
        $expireSeconds = $this->voteInterval * $requestRateLimit;
        $key = $this->keyPrefix.$ipAddress;
        
        try {
            $numRequests = $this->redis->incr($key);
            if ($numRequests === 1) {
                // Only set expire time for the first time.
                $this->redis->expire($key, $expireSeconds);
            }

            if ($numRequests > $requestRateLimit) {
                if ($numRequests % 10 == 0) { 
                    // Write the Log every 10 requests.
                    $this->logger->info('User has reached the request rate limit!', [
                        'client_ip' => $ipAddress,
                        'rate_limit' => $requestRateLimit,
                        'actual_request_times' => $numRequests,
                    ]);
                }
                return false;
            }
            return true;
        } catch (PredisException $e) {
            $this->logger->err('Redis Communication Issue', [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
            ]);
        }
        return false;
    }
    
    public function getRequestRateLimit()
    {
        return $this->requestRateLimit;
    }
}
