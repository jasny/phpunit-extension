<?php

declare(strict_types=1);

namespace Jasny\PHPUnit\Util;

use PHPUnit\Util\RegularExpression;

/**
 * Error handler for expected warnings and notices.
 */
final class ExpectedWarnings
{
    private const TYPES = [
        \E_NOTICE => 'notice',
        \E_USER_NOTICE => 'notice',
        \E_STRICT => 'notice',
        \E_WARNING => 'warning',
        \E_USER_WARNING => 'warning',
        \E_DEPRECATED => 'deprecation',
        \E_USER_DEPRECATED => 'deprecation',
    ];

    /**
     * @var bool
     */
    private $registered = false;

    /**
     * @var callable
     */
    private $oldErrorHandler;

    /**
     * @var array
     */
    private $expected = [];


    /**
     * Add an expected error.
     */
    public function add(string $type, string $message = ''): void
    {
        $this->expected[] = ['type' => $type, 'message' => $message, 'triggered' => false];
    }

    /**
     * Add an expected error with regexp message.
     */
    public function addRegExp(string $type, string $regexp): void
    {
        $this->expected[] = ['type' => $type, 'regexp' => $regexp, 'triggered' => false];
    }

    /**
     * Check if all expected error matches given error.
     */
    private function expectedMatches(array $expected, string $type, string $message): bool
    {
        if ($expected['type'] !== $type || $expected['triggered']) {
            return false;
        }

        return isset($expected['message']) // either 'message' or 'regexp' is set
            ? ($expected['message'] === '' || strpos($expected['message'], $message) !== false)
            : (bool)RegularExpression::safeMatch($expected['regexp'], $message);
    }

    /**
     * Mark an error as triggered.
     * Returns false if there's no expected error that matches.
     */
    private function markTriggered(string $type, string $message): bool
    {
        foreach ($this->expected as &$expected) {
            if ($this->expectedMatches($expected, $type, $message)) {
                $expected['triggered'] = true;
                return true;
            }
        }

        return false;
    }

    /**
     * Get all expected errors that weren't triggered.
     */
    public function getNotTriggered(): array
    {
        return array_filter($this->expected, function (array $expected) {
            return !$expected['triggered'];
        });
    }

    /**
     * Invoke the error handler.
     */
    public function __invoke(int $errorNumber, string $errorString, string $errorFile, int $errorLine): ?bool
    {
        if (isset(self::TYPES[$errorNumber]) && $this->markTriggered(self::TYPES[$errorNumber], $errorString)) {
            return true;
        }

        return ($this->oldErrorHandler)($errorNumber, $errorString, $errorFile, $errorLine);
    }

    /**
     * Register as error handler.
     */
    public function register(): void
    {
        if ($this->registered) {
            return; // @codeCoverageIgnore
        }

        $this->oldErrorHandler = \set_error_handler($this);
        $this->registered = true;
    }

    /**
     * Restore old error handler.
     */
    public function unregister(): void
    {
        if (!$this->registered) {
            return; // @codeCoverageIgnore
        }

        \restore_error_handler();

        $this->registered = false;
        unset($this->oldErrorHandler);
    }

    /**
     * @internal
     */
    public static function describeExpected(array $expected): string
    {
        if (isset($expected['regexp'])) {
            return \sprintf('%s with message matching "%s"', $expected['type'], $expected['regexp']);
        }

        return $expected['message'] === ''
            ? $expected['type']
            : \sprintf('%s with message "%s"', $expected['type'], $expected['message']);
    }
}
