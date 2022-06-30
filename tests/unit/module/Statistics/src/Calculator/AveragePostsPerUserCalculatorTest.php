<?php

namespace Tests\unit\module\Statistics\src\Calculator;

use DateTime;
use DateInterval;
use SocialPost\Dto\SocialPostTo;
use Statistics\Calculator\AveragePostsPerUserCalculator;
use PHPUnit\Framework\TestCase;
use Statistics\Dto\ParamsTo;
use UnexpectedValueException;

class AveragePostsPerUserCalculatorTest extends TestCase
{
    private function createSocialPosts(array $data): array
    {
        $posts = [];

        foreach ($data as $postData) {
            $posts[] = (new SocialPostTo())
                ->setDate($postData['date'])
                ->setAuthorId($postData['authorId']);
        }

        return $posts;
    }

    private function createParams(): ParamsTo
    {
        return (new ParamsTo())
            ->setStartDate((new DateTime())->sub(DateInterval::createFromDateString('1 day')))
            ->setEndDate((new DateTime())->add(DateInterval::createFromDateString('1 day')))
            ->setStatName('Stat Name');
    }

    public function testDoAccumulateUnexpectedValueException(): void
    {
        $post = (new SocialPostTo())
            ->setDate(new DateTime());

        $params = $this->createParams();
        $calculator = new AveragePostsPerUserCalculator();
        $calculator->setParameters($params);

        $this->expectException(UnexpectedValueException::class);
        $calculator->accumulateData($post);
    }

    /**
     * @dataProvider provider
     */
    public function testDoAccumulate(array $data, float $expectedValue): void
    {
        $posts = $this->createSocialPosts($data);

        $params = $this->createParams();
        $calculator = new AveragePostsPerUserCalculator();
        $calculator->setParameters($params);

        foreach ($posts as $post) {
            $calculator->accumulateData($post);
        }

        $statistics = $calculator->calculate();

        $this->assertEquals($expectedValue, $statistics->getValue());
    }

    public function provider(): array
    {
        return [
            [
                'posts' => [],
                'expectedValue' => 0,
            ],
            [
                'posts' => [
                    [
                        'date'     => new DateTime(),
                        'authorId' => 1,
                    ],
                    [
                        'date'     => new DateTime(),
                        'authorId' => 2,
                    ],
                    [
                        'date'     => new DateTime(),
                        'authorId' => 3,
                    ],
                    [
                        'date'     => new DateTime(),
                        'authorId' => 4,
                    ],
                    [
                        'date'     => new DateTime(),
                        'authorId' => 5,
                    ],

                ],
                'expectedValue' => 1,
            ],
            [
                'posts' => [
                    [
                        'date'     => new DateTime(),
                        'authorId' => 1,
                    ],
                    [
                        'date'     => new DateTime(),
                        'authorId' => 1,
                    ],
                    [
                        'date'     => new DateTime(),
                        'authorId' => 1,
                    ],
                    [
                        'date'     => new DateTime(),
                        'authorId' => 1,
                    ],
                    [
                        'date'     => new DateTime(),
                        'authorId' => 1,
                    ],

                ],
                'expectedValue' => 5,
            ],
            [
                'posts' => [
                    [
                        'date'     => new DateTime(),
                        'authorId' => 1,
                    ],
                    [
                        'date'     => new DateTime(),
                        'authorId' => 1,
                    ],
                    [
                        'date'     => new DateTime(),
                        'authorId' => 1,
                    ],
                    [
                        'date'     => new DateTime(),
                        'authorId' => 2,
                    ],
                    [
                        'date'     => new DateTime(),
                        'authorId' => 3,
                    ],

                ],
                'expectedValue' => 1.67,
            ]
        ];
    }
}
