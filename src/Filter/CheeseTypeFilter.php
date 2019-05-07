<?php

namespace App\Filter;

use ApiPlatform\Core\Api\FilterInterface;

class CheeseTypeFilter implements FilterInterface {

    const STRATEGY_EXACT = 'exact';

    protected $properties;

    public function __construct(array $properties = null)
    {
        $this->properties = $properties;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(string $resourceClass): array
    {
        $description = [];

        $properties = $this->properties;

        foreach ($properties as $property => $strategy) {

            $filterParameterNames = [
                $property
            ];

            foreach ($filterParameterNames as $filterParameterName) {
                $description[$filterParameterName] = [
                    'property' => $property,
                    'type' => 'string',
                    'required' => false,
                    'strategy' => self::STRATEGY_EXACT,
                    'is_collection' =>  false,
                ];
            }
        }

        return $description;
    }
}