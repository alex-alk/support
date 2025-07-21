<?php

namespace Support\Cache;

use Psr\Cache\CacheItemInterface;
use DateTime;
use DateInterval;

class FileCacheItem implements CacheItemInterface
{
    private string $key;
    private mixed $value;
    private bool $hit;
    private ?DateTime $expiration;

    public function __construct(string $key, mixed $value = null, bool $hit = false, ?DateTime $expiration = null)
    {
        $this->key = $key;
        $this->value = $value;
        $this->hit = $hit;
        $this->expiration = $expiration;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function get(): mixed
    {
        return $this->isHit() ? $this->value : null;
    }

    public function isHit(): bool
    {
        if ($this->expiration !== null && new DateTime() > $this->expiration) {
            return false;
        }
        return $this->hit;
    }

    public function set($value): static
    {
        $this->value = $value;
        $this->hit = true;
        return $this;
    }

    public function expiresAt($expiration): static
    {
        $this->expiration = $expiration instanceof DateTime ? $expiration : null;
        return $this;
    }

    public function expiresAfter($time): static
    {
        if (is_int($time)) {
            $this->expiration = (new DateTime())->add(new DateInterval("PT{$time}S"));
        } elseif ($time instanceof DateInterval) {
            $this->expiration = (new DateTime())->add($time);
        } else {
            $this->expiration = null;
        }
        return $this;
    }

    public function getExpiration(): ?DateTime
    {
        return $this->expiration;
    }
}
