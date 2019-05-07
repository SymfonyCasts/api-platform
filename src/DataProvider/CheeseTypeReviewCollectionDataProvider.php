<?php

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Api\Model\CheeseTypeReview;

final class CheeseTypeReviewCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return CheeseTypeReview::class === $resourceClass;
    }

    public function getCollection(string $resourceClass, string $operationName = null): \Generator
    {
        // Retrieve the Cheese Type Reviews  collection from somewhere
        yield new CheeseTypeReview(1, "Soft", "Suberb tasting cheese, extra nice if it is made from a sheep's milk");
        yield new CheeseTypeReview(2, "Hard", "Great on burgers and on grilled sandwiches with ham (Tosti)");
        yield new CheeseTypeReview(3, "Blue", "Delicious cheeses, strong flavor with a great lenght and fresh finish");
    }
}