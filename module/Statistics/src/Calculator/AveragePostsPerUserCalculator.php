<?php

declare(strict_types = 1);

namespace Statistics\Calculator;

use SocialPost\Dto\SocialPostTo;
use Statistics\Dto\StatisticsTo;
use UnexpectedValueException;

class AveragePostsPerUserCalculator extends AbstractCalculator
{
    protected const UNITS = 'posts';

    private array $postsByUser = [];

    /**
     * @inheritDoc
     */
    protected function doAccumulate(SocialPostTo $postTo): void
    {
        $authorId = $postTo->getAuthorId();
        if (null === $authorId) {
            throw new UnexpectedValueException('authorId cannot be null');
        }

        $this->postsByUser[$authorId] = $this->postsByUser[$authorId] ?? 0;
        $this->postsByUser[$authorId]++;
    }

    /**
     * @inheritDoc
     */
    protected function doCalculate(): StatisticsTo
    {
        $value = count($this->postsByUser) > 0
            ? (array_sum($this->postsByUser) / count($this->postsByUser))
            : 0;

        return (new StatisticsTo())->setValue(round($value,2));
    }
}
