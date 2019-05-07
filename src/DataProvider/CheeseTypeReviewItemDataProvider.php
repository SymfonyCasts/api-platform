<?php

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Api\Model\CheeseTypeReview;

final class CheeseTypeReviewItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return CheeseTypeReview::class === $resourceClass;
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = [])
    {
        if ($id === 1) {
            yield new CheeseTypeReview(1, "Soft", "Suberb tasting cheese, extra nice if it is made from a sheep's milk");
        }
        if ($id === 2) {
            yield new CheeseTypeReview(2, "Hard", "Great on burgers and on grilled sandwiches with ham (Tosti)");
        }
        if ($id === 3) {
            yield new CheeseTypeReview(3, "Blue", "Delicious cheeses, strong flavor with a great lenght and fresh finish");
        }
    }
}
