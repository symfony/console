<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Console\Question;

use Symfony\Component\Console\Exception\InvalidArgumentException;

/**
 * Represents a question that accepts file input (paste or path).
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class FileQuestion extends Question
{
    /** @var string[] */
    private array $allowedMimeTypes;
    private ?int $maxFileSize;
    private bool $allowPaste;
    private bool $allowPath;

    public function __construct(
        string $question,
        array $allowedMimeTypes = [],
        ?int $maxFileSize = 5 * 1024 * 1024,
        bool $allowPaste = true,
        bool $allowPath = true,
    ) {
        parent::__construct($question);

        if (!$allowPaste && !$allowPath) {
            throw new InvalidArgumentException('At least one of allowPaste or allowPath must be true.');
        }

        $this->allowedMimeTypes = $allowedMimeTypes;
        $this->maxFileSize = $maxFileSize;
        $this->allowPaste = $allowPaste;
        $this->allowPath = $allowPath;

        $this->setTrimmable(false);
    }

    /**
     * @return string[]
     */
    public function getAllowedMimeTypes(): array
    {
        return $this->allowedMimeTypes;
    }

    public function getMaxFileSize(): ?int
    {
        return $this->maxFileSize;
    }

    public function isPasteAllowed(): bool
    {
        return $this->allowPaste;
    }

    public function isPathAllowed(): bool
    {
        return $this->allowPath;
    }

    public function isMimeTypeAllowed(string $mimeType): bool
    {
        if (!$this->allowedMimeTypes) {
            return true;
        }

        foreach ($this->allowedMimeTypes as $allowedType) {
            if ($mimeType === $allowedType) {
                return true;
            }

            if (str_ends_with($allowedType, '/*')) {
                $prefix = substr($allowedType, 0, -1);
                if (str_starts_with($mimeType, $prefix)) {
                    return true;
                }
            }
        }

        return false;
    }
}
