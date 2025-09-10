<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Console\Attribute;

use Symfony\Component\Console\Attribute\Reflection\ReflectionMember;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

#[\Attribute(\Attribute::TARGET_PARAMETER | \Attribute::TARGET_PROPERTY)]
class Ask implements InteractiveAttributeInterface
{
    public ?\Closure $validator;
    private \Closure $closure;

    /**
     * @param string                     $question    The question to ask the user
     * @param string|bool|int|float|null $default     The default answer to return if the user enters nothing
     * @param bool                       $hidden      Whether the user response must be hidden or not
     * @param bool                       $multiline   Whether the user response should accept newline characters
     * @param bool                       $trimmable   Whether the user response must be trimmed or not
     * @param int|null                   $timeout     The maximum time the user has to answer the question in seconds
     * @param callable|null              $validator   The validator for the question
     * @param int|null                   $maxAttempts The maximum number of attempts allowed to answer the question.
     *                                                Null means an unlimited number of attempts
     */
    public function __construct(
        public string $question,
        public string|bool|int|float|null $default = null,
        public bool $hidden = false,
        public bool $multiline = false,
        public bool $trimmable = true,
        public ?int $timeout = null,
        ?callable $validator = null,
        public ?int $maxAttempts = null,
    ) {
        $this->validator = $validator ? $validator(...) : null;
    }

    /**
     * @internal
     */
    public static function tryFrom(\ReflectionParameter|\ReflectionProperty $member, string $name): ?self
    {
        $reflection = new ReflectionMember($member);

        if (!$self = $reflection->getAttribute(self::class)) {
            return null;
        }

        $self->closure = function (SymfonyStyle $io, InputInterface $input) use ($self, $reflection, $name) {
            if (($reflection->isProperty() && isset($this->{$reflection->getName()})) || ($reflection->isParameter() && null !== $input->getArgument($name))) {
                return;
            }

            $question = new Question($self->question, $self->default);
            $question->setHidden($self->hidden);
            $question->setMultiline($self->multiline);
            $question->setTrimmable($self->trimmable);
            $question->setTimeout($self->timeout);

            if (!$self->validator && $reflection->isProperty()) {
                $self->validator = function (mixed $value) use ($reflection): mixed {
                    return $this->{$reflection->getName()} = $value;
                };
            }

            $question->setValidator($self->validator);
            $question->setMaxAttempts($self->maxAttempts);

            if ($reflection->isBackedEnumType()) {
                /** @var class-string<\BackedEnum> $backedType */
                $backedType = $reflection->getType()->getName();
                $question->setNormalizer(fn (string|int $value) => $backedType::tryFrom($value) ?? throw InvalidArgumentException::fromEnumValue($reflection->getName(), $value, array_map(fn (\BackedEnum $enum): string|int => $enum->value, $backedType::cases())));
            }

            $value = $io->askQuestion($question);

            if (null === $value && !$reflection->isNullable()) {
                return;
            }

            if ($reflection->isProperty()) {
                $this->{$reflection->getName()} = $value;
            } else {
                $input->setArgument($name, $value);
            }
        };

        return $self;
    }

    /**
     * @internal
     */
    public function getFunction(object $instance): \ReflectionFunction
    {
        return new \ReflectionFunction($this->closure->bindTo($instance, $instance::class));
    }
}
