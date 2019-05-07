<?php

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Api\Model\CheeseTypeReview;
use Symfony\Component\HttpFoundation\RequestStack;

final class CheeseTypeReviewCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{

    protected $request;

    public function __construct(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return CheeseTypeReview::class === $resourceClass;
    }

    public function getCollection(string $resourceClass, string $operationName = null): \Generator
    {
        $cheeseTypeFilter = $this->request->query->get('cheeseType');

        if (!isset($cheeseTypeFilter) || $cheeseTypeFilter === 'Soft') {
            yield new CheeseTypeReview(1, "Soft", "Suberb tasting cheese, extra nice if it is made from a sheep's milk");
        }
        if(!isset($cheeseTypeFilter) || $cheeseTypeFilter === 'Hard') {
            yield new CheeseTypeReview(2, "Hard", "Great on burgers and on grilled sandwiches with ham (Tosti)");
        }
        if (!isset($cheeseTypeFilter) || $cheeseTypeFilter === 'Blue') {
            yield new CheeseTypeReview(3, "Blue", "Delicious cheeses, strong flavor with a great lenght and fresh finish");
        }
    }
}