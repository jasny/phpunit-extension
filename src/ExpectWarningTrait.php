<?php

declare(strict_types=1);

namespace Jasny\PHPUnit;

use Jasny\PHPUnit\Util\ExpectedWarnings;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\TestStatus\TestStatus;

trait ExpectWarningTrait
{
    protected ExpectedWarnings|null $expectedWarnings;

    abstract public function status(): TestStatus;
    abstract public function addToAssertionCount(int $count);

    #[Before]
    protected function registerExpectedWarnings(): void
    {
        $this->expectedWarnings = new ExpectedWarnings();
        $this->expectedWarnings->register();
    }

    #[After]
    protected function unregisterExpectedWarnings(): void
    {
        if ($this->expectedWarnings === null) {
            return;
        }

        $this->expectedWarnings->unregister();
        $this->expectedWarnings = null;
    }

    /**
     * Performs assertions shared by all tests of a test cas.
     */
    protected function assertPostConditions(): void
    {
        if ($this->expectedWarnings === null) {
            return;
        }

        if (!$this->status()->isSuccess() && !$this->status()->isRisky() && !$this->status()->isUnknown()) {
            return; // @codeCoverageIgnore
        }

        $notTriggered = $this->expectedWarnings->getNotTriggered();

        if ($notTriggered !== []) {
            // @codeCoverageIgnoreStart
            $desc = ExpectedWarnings::describeExpected(reset($notTriggered));
            throw new AssertionFailedError(\sprintf('Failed asserting that %s is triggered', $desc));
            // @codeCoverageIgnoreEnd
        }
    }

    public function expectDeprecation(): void
    {
        $this->expectedWarnings->add('deprecation');
        $this->addToAssertionCount(1);
    }

    public function expectDeprecationMessage(string $message): void
    {
        $this->expectedWarnings->add('deprecation', $message);
        $this->addToAssertionCount(1);
    }

    public function expectDeprecationMessageMatches(string $regularExpression): void
    {
        $this->expectedWarnings->addRegExp('deprecation', $regularExpression);
        $this->addToAssertionCount(1);
    }

    public function expectNotice(): void
    {
        $this->expectedWarnings->add('notice');
        $this->addToAssertionCount(1);
    }

    public function expectNoticeMessage(string $message): void
    {
        $this->expectedWarnings->add('notice', $message);
        $this->addToAssertionCount(1);
    }

    public function expectNoticeMessageMatches(string $regularExpression): void
    {
        $this->expectedWarnings->addRegExp('notice', $regularExpression);
        $this->addToAssertionCount(1);
    }

    public function expectWarning(): void
    {
        $this->expectedWarnings->add('warning');
        $this->addToAssertionCount(1);
    }

    public function expectWarningMessage(string $message): void
    {
        $this->expectedWarnings->add('warning', $message);
        $this->addToAssertionCount(1);
    }

    public function expectWarningMessageMatches(string $regularExpression): void
    {
        $this->expectedWarnings->addRegExp('warning', $regularExpression);
        $this->addToAssertionCount(1);
    }
}
