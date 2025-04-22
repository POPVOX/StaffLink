<?php

declare(strict_types=1);

namespace ParaTest\Tests\fixtures\extension_run_before_data_provider;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ExtensionMustRunBeforeDataProviderTest extends TestCase
{
    public static string $var = 'foo';

    #[DataProvider('provide')]
    public function testExtensionMustRunBeforeDataProvider(string $var): void
    {
        self::assertSame('bar', $var);
    }

    /** @return iterable<array{string}> */
    public static function provide(): iterable
    {
        if (self::$var !== 'bar') {
            throw new RuntimeException('Extension did not run before data provider.');
        }

        yield [self::$var];
    }
}
