<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Console\Tests\SignalRegistry;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\SignalRegistry\SignalMap;

class SignalMapTest extends TestCase
{
    /**
     * @requires extension pcntl
     * @dataProvider provideSignals
     */
    public function testSignalExists(int $signal, string $expected)
    {
        $this->assertSame($expected, SignalMap::getSignalName($signal));
    }

    public function provideSignals()
    {
        yield [\SIGINT, 'SIGINT'];
        yield [\SIGKILL, 'SIGKILL'];
        yield [\SIGTERM, 'SIGTERM'];
        yield [\SIGSYS, 'SIGSYS'];
    }

    public function testSignalDoesNotExist()
    {
        $this->assertNull(SignalMap::getSignalName(999999));
    }
}
