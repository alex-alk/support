<?php
namespace Fig\Cache;

/**
 * Key validator trait
 */
trait KeyValidatorTrait
{

    /**
     * Characters which cannot be used in cache key.
     *
     * The characters returned by this function are reserved for future extensions and MUST NOT be
     * supported by implementing libraries
     *
     * @return string
     */
    final public function getReservedKeyCharacters(): string
    {
        return '{}()/\@:';
    }

    /**
     * Determines if the specified key is legal under PSR-6.
     *
     * @param string $key
     *   The key to validate.
     * @throws InvalidArgumentException
     *   An exception implementing The Cache InvalidArgumentException interface
     *   will be thrown if the key does not validate.
     * @return bool
     *   TRUE if the specified key is legal.
     */
    protected function validateKey($key): bool
    {
        if (!is_string($key) || $key === '') {
            throw new InvalidArgumentException('Key should be a non empty string');
        }

        $unsupportedMatched = preg_match('#[' . preg_quote($this->getReservedKeyCharacters()) . ']#', $key);
        if ($unsupportedMatched > 0) {
            throw new InvalidArgumentException('Can\'t validate the specified key');
        }

        return true;
    }
}