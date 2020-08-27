<?php

namespace App\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use ApiPlatform\Core\Validator\ValidatorInterface;
use App\Dto\CheeseListingInput;
use App\Entity\CheeseListing;

class CheeseListingToInputDataTransformer implements DataTransformerInterface
{
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }


    /**
     * @param CheeseListingInput $input
     */
    public function transform($input, string $to, array $context = [])
    {
        $this->validator->validate($input);

        if (isset($context['object_to_populate'])) {
            $cheeseListing = $context['object_to_populate'];
        } else {
            $cheeseListing = new CheeseListing($input->title);
        }

        $cheeseListing->setDescription($input->description);
        $cheeseListing->setPrice($input->price);
        if ($input->owner) {
            $cheeseListing->setOwner($input->owner);
        }

        return $cheeseListing;
    }

    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        if ($data instanceof CheeseListing) {
            // already transformed
            return false;
        }

        return $to === CheeseListing::class && ($context['input']['class'] ?? null) === CheeseListingInput::class;
    }
}
