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

/**
 * Represents a yes/no question.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ConfirmationQuestion extends Question
{
    private $trueAnswer;
    
    public function __construct($question, $default = true, $trueAnswer = 'y')
    {
        parent::__construct($question, (bool) $default);

        $this->trueAnswer = $trueAnswer;
        $this->setNormalizer($this->getDefaultNormalizer());
    }

    private function getDefaultNormalizer()
    {
        $default = $this->getDefault();

        return function ($answer) use ($default) {
            if (is_bool($answer)) {
                return $answer;
            }

            if (false === $default) {
                return $answer && $this->trueAnswer === strtolower($answer[0]);
            }

            return !$answer || $this->trueAnswer === strtolower($answer[0]);
        };
    }
}
