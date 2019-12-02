<?php

declare(strict_types=1);

namespace Jasny\PHPUnit\Tests;

use Jasny\PHPUnit\ExpectWarningTrait;
use Jasny\PHPUnit\Util\ExpectedWarnings;
use PHPUnit\Framework\Error\Warning;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\PHPUnit\ExpectWarningTrait
 * @covers \Jasny\PHPUnit\Util\ExpectedWarnings
 */
class ExpectWarningTraitTest extends TestCase
{
    use ExpectWarningTrait;

    public function typeProvider()
    {
        return [
            'notice' => [E_USER_NOTICE, 'Notice'],
            'warning' => [E_USER_WARNING, 'Warning'],
            'deprecation' => [E_USER_DEPRECATED, 'Deprecation'],
        ];
    }

    private function assertNotTriggered(int $count)
    {
        $notTriggered = $this->expectedWarnings->getNotTriggered();
        $this->expectedWarnings = null;

        $this->assertCount($count, $notTriggered);
    }

    /**
     * @dataProvider typeProvider
     */
    public function testExpectNotice(int $errno, string $type): void
    {
        $this->{"expect{$type}"}();
        trigger_error("Some error", $errno);
    }

    /**
     * @dataProvider typeProvider
     */
    public function testExpectNoticeMessage(int $errno, string $type): void
    {
        $this->{"expect{$type}Message"}("Some error");
        trigger_error("Some error", $errno);
    }

    /**
     * @dataProvider typeProvider
     */
    public function testExpectNoticeMessageMatches(int $errno, string $type): void
    {
        $this->{"expect{$type}MessageMatches"}("/some err(or)?/i");
        trigger_error("Some error", $errno);
    }


    public function testNotTriggered()
    {
        $this->expectNotice();
        $this->expectDeprecationMessageMatches('/err(or)?/');
        $this->expectWarningMessage("Some error");

        $this->assertNotTriggered(3);
    }

    public function testNoExpectedWarning()
    {
        $this->expectNotToPerformAssertions();

        try {
            trigger_error("Some error", E_USER_WARNING);
        } catch (Warning $notice) {
        }
    }

    public function testUnexpectedWarning()
    {
        $this->expectNotice();
        $this->expectDeprecationMessageMatches('/err(or)?/');
        $this->expectWarningMessage("Some error");

        try {
            trigger_error("Other error", E_USER_WARNING);
        } catch (Warning $notice) {
        }

        $this->assertNotTriggered(3);
    }


    public function unexpectedProvider()
    {
        return [
            'plain' => ['warning', ['type' => 'warning', 'message' => '']],
            'message' => ['warning with message "foo"', ['type' => 'warning', 'message' => 'foo']],
            'regexp' => ['warning with message matching "/a(b|c)/"', ['type' => 'warning', 'regexp' => '/a(b|c)/']],
        ];
    }

    /**
     * @dataProvider unexpectedProvider
     */
    public function testDescribeExpected(string $description, array $warning)
    {
        $this->assertEquals($description, ExpectedWarnings::describeExpected($warning));
    }
}
