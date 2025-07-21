<?php

namespace Support\Cache;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\CacheItemInterface;
use Fig\Cache\BasicPoolTrait;
use Fig\Cache\KeyValidatorTrait;

class FilePool implements CacheItemPoolInterface
{
    use BasicPoolTrait;
    use KeyValidatorTrait;

    private string $cacheDir;

    public function __construct(string $cacheDir)
    {
        $this->cacheDir = rtrim($cacheDir, '/');
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
    }

    protected function getPath(string $key): string
    {
        return $this->cacheDir . '/' . sha1($key) . '.cache';
    }

    protected function internalGetItem(string $key): CacheItemInterface
    {
        $path = $this->getPath($key);

        if (!file_exists($path)) {
            return new FileCacheItem($key, null, false);
        }

        $data = @unserialize(file_get_contents($path));

        if (!is_array($data) || !array_key_exists('value', $data)) {
            return new FileCacheItem($key, null, false);
        }

        $expiration = isset($data['expiration']) ? new \DateTime($data['expiration']) : null;
        $hit = $expiration === null || new \DateTime() <= $expiration;

        return new FileCacheItem($key, $data['value'], $hit, $expiration);
    }

    protected function internalHasItem(string $key): bool
    {
        return $this->internalGetItem($key)->isHit();
    }

    protected function internalSaveItem(CacheItemInterface $item): bool
    {
        $path = $this->getPath($item->getKey());

        $data = [
            'value' => $item->get(),
            'expiration' => method_exists($item, 'getExpiration') && $item->getExpiration()
                ? $item->getExpiration()->format(DATE_ATOM)
                : null,
        ];

        return file_put_contents($path, serialize($data)) !== false;
    }

    protected function internalDeleteItem(string $key): bool
    {
        $path = $this->getPath($key);
        return file_exists($path) ? unlink($path) : true;
    }

    protected function internalClear(): bool
    {
        foreach (glob("{$this->cacheDir}/*.cache") as $file) {
            @unlink($file);
        }
        return true;
    }
}
