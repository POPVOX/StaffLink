<?php

declare(strict_types=1);

namespace ParaTest\Tests\fixtures\extension_run_before_data_provider;

use PHPUnit\Event;
use PHPUnit\Runner;
use PHPUnit\TextUI;

final class PHPUnitExtension implements Runner\Extension\Extension
{
    public function bootstrap(
        TextUI\Configuration\Configuration $configuration,
        Runner\Extension\Facade $facade,
        Runner\Extension\ParameterCollection $parameters,
    ): void {
        $facade->registerSubscribers(
            new class implements Event\Test\DataProviderMethodCalledSubscriber {
                public function notify(Event\Test\DataProviderMethodCalled $event): void
                {
                    ExtensionMustRunBeforeDataProviderTest::$var = 'bar';
                }
            },
        );
    }
}
