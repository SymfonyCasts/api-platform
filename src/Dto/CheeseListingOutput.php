<?php

namespace App\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

class CheeseListingOutput
{
    /**
     * The title of this listing
     *
     * @Groups({"cheese:read"})
     * @var string
     */
    public $title;

    /**
     * @var string
     * @Groups({"cheese:read"})
     */
    public $description;

    /**
     * @var integer
     * @Groups({"cheese:read"})
     */
    public $price;
}
