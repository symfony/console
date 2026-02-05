<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Console\Tests\Question;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Question\FileQuestion;

class FileQuestionTest extends TestCase
{
    public function testGetQuestion()
    {
        $question = new FileQuestion('Provide a file:');

        $this->assertSame('Provide a file:', $question->getQuestion());
    }

    public function testDefaultOptions()
    {
        $question = new FileQuestion('Provide a file:');

        $this->assertSame([], $question->getAllowedMimeTypes());
        $this->assertSame(5 * 1024 * 1024, $question->getMaxFileSize());
        $this->assertTrue($question->isPasteAllowed());
        $this->assertTrue($question->isPathAllowed());
        $this->assertFalse($question->isTrimmable());
    }

    public function testWithAllowedMimeTypes()
    {
        $question = new FileQuestion('Provide a file:', ['image/png', 'image/jpeg']);

        $this->assertSame(['image/png', 'image/jpeg'], $question->getAllowedMimeTypes());
    }

    public function testWithMaxFileSize()
    {
        $question = new FileQuestion('Provide a file:', [], 1024 * 1024);

        $this->assertSame(1024 * 1024, $question->getMaxFileSize());
    }

    public function testWithAllowPasteFalse()
    {
        $question = new FileQuestion('Provide a file:', [], null, false);

        $this->assertFalse($question->isPasteAllowed());
    }

    public function testWithAllowPathFalse()
    {
        $question = new FileQuestion('Provide a file:', [], null, true, false);

        $this->assertFalse($question->isPathAllowed());
    }

    public function testBothPasteAndPathFalseThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one of allowPaste or allowPath must be true.');

        new FileQuestion('Provide a file:', [], null, false, false);
    }

    #[DataProvider('provideMimeTypeMatches')]
    public function testIsMimeTypeAllowed(array $allowedTypes, string $mimeType, bool $expected)
    {
        $question = new FileQuestion('Provide a file:', $allowedTypes);

        $this->assertSame($expected, $question->isMimeTypeAllowed($mimeType));
    }

    public static function provideMimeTypeMatches(): iterable
    {
        // Empty allowed types allows all
        yield 'empty allows all' => [[], 'image/png', true];
        yield 'empty allows all - text' => [[], 'text/plain', true];

        // Exact matches
        yield 'exact match png' => [['image/png'], 'image/png', true];
        yield 'exact match jpeg' => [['image/jpeg'], 'image/jpeg', true];
        yield 'no match' => [['image/png'], 'image/jpeg', false];

        // Multiple allowed types
        yield 'multiple - first match' => [['image/png', 'image/jpeg'], 'image/png', true];
        yield 'multiple - second match' => [['image/png', 'image/jpeg'], 'image/jpeg', true];
        yield 'multiple - no match' => [['image/png', 'image/jpeg'], 'image/gif', false];

        // Wildcard matches
        yield 'wildcard image/*' => [['image/*'], 'image/png', true];
        yield 'wildcard image/* - jpeg' => [['image/*'], 'image/jpeg', true];
        yield 'wildcard image/* - gif' => [['image/*'], 'image/gif', true];
        yield 'wildcard no match' => [['image/*'], 'application/pdf', false];

        // Mixed exact and wildcard
        yield 'mixed - exact match' => [['application/pdf', 'image/*'], 'application/pdf', true];
        yield 'mixed - wildcard match' => [['application/pdf', 'image/*'], 'image/png', true];
        yield 'mixed - no match' => [['application/pdf', 'image/*'], 'text/plain', false];
    }

    public function testIsNotTrimmableByDefault()
    {
        $question = new FileQuestion('Provide a file:');

        $this->assertFalse($question->isTrimmable());
    }
}
