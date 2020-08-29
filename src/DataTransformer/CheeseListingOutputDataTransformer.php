<?php

namespace App\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use App\Dto\CheeseListingOutput;
use App\Entity\CheeseListing;

class CheeseListingOutputDataTransformer implements DataTransformerInterface
{
    /**
     * @param CheeseListing $cheeseListing
     */
    public function transform($cheeseListing, string $to, array $context = [])
    {
        $output = new CheeseListingOutput();
        $output->title = $cheeseListing->getTitle();

        return $output;
    }

    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        return $data instanceof CheeseListing && $to === CheeseListingOutput::class;
    }
}
