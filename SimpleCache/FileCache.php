<?php

namespace Your\Cache;

use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * A simple file-based PSR-16 cache implementation.
 */
class FileCache implements CacheInterface
{
    private string $directory;

    /**
     * @param string $directory Path to writable cache directory
     * @throws \RuntimeException If directory does not exist or is not writable
     */
    public function __construct(string $directory)
    {
        if (!is_dir($directory) || !is_writable($directory)) {
            throw new \RuntimeException("Cache directory '$directory' does not exist or is not writable.");
        }

        $this->directory = rtrim($directory, DIRECTORY_SEPARATOR);
    }

    /** {@inheritdoc} */
    public function get(string $key, mixed $default = null): mixed
    {
        $this->validateKey($key);
        $path = $this->getPath($key);

        if (!file_exists($path)) {
            return $default;
        }

        $content = @file_get_contents($path);
        $data = @unserialize($content);
        if (!is_array($data) || !isset($data['expires'], $data['value'])) {
            return $default;
        }

        if ($data['expires'] !== 0 && time() > $data['expires']) {
            @unlink($path);
            return $default;
        }

        return $data['value'];
    }

    /** {@inheritdoc} */
    public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool
    {
        $this->validateKey($key);

        $expires = 0;
        if ($ttl !== null) {
            if ($ttl instanceof \DateInterval) {
                $expires = (new \DateTime())->add($ttl)->getTimestamp();
            } else {
                $expires = time() + $ttl;
            }
        }

        $data = ['expires' => $expires, 'value' => $value];
        $path = $this->getPath($key);

        return (file_put_contents($path, serialize($data)) !== false);
    }

    /** {@inheritdoc} */
    public function delete(string $key): bool
    {
        $this->validateKey($key);
        $path = $this->getPath($key);

        return !file_exists($path) || @unlink($path);
    }

    /** {@inheritdoc} */
    public function clear(): bool
    {
        $files = glob($this->directory . DIRECTORY_SEPARATOR . '*.cache');
        $ok = true;

        foreach ($files as $file) {
            if (!@unlink($file)) {
                $ok = false;
            }
        }

        return $ok;
    }

    /** {@inheritdoc} */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $results = [];
        foreach ($keys as $key) {
            $results[$key] = $this->get($key, $default);
        }

        return $results;
    }

    /** {@inheritdoc} */
    public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool
    {
        $ok = true;
        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $ttl)) {
                $ok = false;
            }
        }

        return $ok;
    }

    /** {@inheritdoc} */
    public function deleteMultiple(iterable $keys): bool
    {
        $ok = true;
        foreach ($keys as $key) {
            if (!$this->delete($key)) {
                $ok = false;
            }
        }

        return $ok;
    }

    /** {@inheritdoc} */
    public function has(string $key): bool
    {
        $this->validateKey($key);
        $path = $this->getPath($key);

        if (!file_exists($path)) {
            return false;
        }

        $content = @file_get_contents($path);
        $data = @unserialize($content);
        if (!is_array($data) || !isset($data['expires'])) {
            return false;
        }

        if ($data['expires'] !== 0 && time() > $data['expires']) {
            @unlink($path);
            return false;
        }

        return true;
    }

    /**
     * Generate filesystem path for a cache key.
     */
    private function getPath(string $key): string
    {
        $filename = preg_replace('/[^A-Za-z0-9_\.]/', '_', $key);
        return $this->directory . DIRECTORY_SEPARATOR . $filename . '.cache';
    }

    /**
     * Validate cache key according to PSR-16.
     *
     * @throws InvalidArgumentException for invalid keys
     */
    private function validateKey(string $key): void
    {
        if ($key === '' || preg_match('/[{}()\/\\@:\s]/', $key)) {
            throw new class("Invalid cache key: $key") extends \InvalidArgumentException implements InvalidArgumentException {};
        }
    }
}
