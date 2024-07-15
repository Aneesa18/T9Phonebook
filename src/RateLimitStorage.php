<?php
namespace App;

/**
 * RateLimitStorage class for limiting the number of requests by a user in an hour.
 */
class RateLimitStorage
{
    /**
     * Gets a value from the storage by key.
     *
     * @param string $key The key to identify the value.
     * @return mixed The value stored under the given key or null if not found.
     */
    public function get(string $key): mixed
    {
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }
        return null;
    }

    /**
     * Sets a value in the storage with a given key.
     *
     * @param string $key The key to identify the value.
     * @param mixed $value The value to be stored.
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Checks if the given identifier is allowed based on the rate limit.
     *
     * @param string $identifier The unique identifier for the rate limit.
     * @param int $limit The maximum number of allowed requests within the time window.
     * @param int $timeWindow The time window in seconds for the rate limit.
     * @return bool Returns true if the identifier is allowed and false if not.
     */
    public function isAllowed(string $identifier, int $limit, int $timeWindow): bool
    {
        $key = $identifier . '_' . ceil(time() / $timeWindow); // Key based on identifier and current time window

        $requests = $this->get($key);
        if ($requests === null) {
            $requests = 0;
        }

        if ($requests >= $limit) {
            error_log("Rate limit exceeded for $identifier");
            return false;
        }

        $requests++;
        $this->set($key, $requests);

        return true;
    }
}
