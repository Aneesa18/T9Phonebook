<?php

use PHPUnit\Framework\TestCase;
use App\RateLimitStorage;

class RateLimitStorageTest extends TestCase
{
    public function testRateLimit()
    {
        $rateLimitStorage = new RateLimitStorage();
        $identifier = 'test-user';
        $limit = 2; // 2 requests allowed
        $timeWindow = 60; // 1 minute

        // Asserting that the first two attempts are allowed
        $this->assertTrue($rateLimitStorage->isAllowed($identifier, $limit, $timeWindow));
        $this->assertTrue($rateLimitStorage->isAllowed($identifier, $limit, $timeWindow));

        // Asserting that the third attempt is denied
        $this->assertFalse($rateLimitStorage->isAllowed($identifier, $limit, $timeWindow));
    }
}
