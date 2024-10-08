<?php

declare(strict_types=1);

namespace Jasny\PHPUnit\Tests;

use Jasny\PHPUnit\ExpectWarningTrait;
use Jasny\PHPUnit\Util\ExpectedWarnings;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ExpectedWarnings::class)]
#[CoversTrait(ExpectWarningTrait::class)]
class ExpectWarningTraitTest extends TestCase
{
    use ExpectWarningTrait;

    public static function typeProvider(): array
    {
        return [
            'notice' => [E_USER_NOTICE, 'Notice'],
            'warning' => [E_USER_WARNING, 'Warning'],
            'deprecation' => [E_USER_DEPRECATED, 'Deprecation'],
        ];
    }

    private function assertNotTriggered(int $count): void
    {
        $notTriggered = $this->expectedWarnings->getNotTriggered();
        $this->expectedWarnings->unregister();
        $this->expectedWarnings = null;

        $this->assertCount($count, $notTriggered);
    }

    #[DataProvider('typeProvider')]
    public function testExpectNotice(int $errno, string $type): void
    {
        $this->{"expect{$type}"}();
        trigger_error("Some error", $errno);
    }

    #[DataProvider('typeProvider')]
    public function testExpectNoticeMessage(int $errno, string $type): void
    {
        $this->{"expect{$type}Message"}("Some error");
        trigger_error("Some error", $errno);
    }

    #[DataProvider('typeProvider')]
    public function testExpectNoticeMessageMatches(int $errno, string $type): void
    {
        $this->{"expect{$type}MessageMatches"}("/some err(or)?/i");
        trigger_error("Some error", $errno);
    }

    public function testNotTriggered(): void
    {
        $this->expectNotice();
        $this->expectDeprecationMessageMatches('/err(or)?/');
        $this->expectWarningMessage("Some error");

        $this->assertNotTriggered(3);
    }

    public static function unexpectedProvider(): array
    {
        return [
            'plain' => ['warning', ['type' => 'warning', 'message' => '']],
            'message' => ['warning with message "foo"', ['type' => 'warning', 'message' => 'foo']],
            'regexp' => ['warning with message matching "/a(b|c)/"', ['type' => 'warning', 'regexp' => '/a(b|c)/']],
        ];
    }

    #[DataProvider('unexpectedProvider')]
    public function testDescribeExpected(string $description, array $warning)
    {
        $this->assertEquals($description, ExpectedWarnings::describeExpected($warning));
    }
}
