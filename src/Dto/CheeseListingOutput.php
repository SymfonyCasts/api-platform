<?php

namespace App\Dto;

use Carbon\Carbon;
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

    /**
     * @Groups("cheese:read")
     */
    public function getShortDescription(): ?string
    {
        if (strlen($this->description) < 40) {
            return $this->description;
        }

        return substr($this->description, 0, 40).'...';
    }

    /**
     * How long ago in text that this cheese listing was added.
     *
     * @Groups("cheese:read")
     */
    public function getCreatedAtAgo(): string
    {
        return Carbon::instance($this->getCreatedAt())->diffForHumans();
    }
}
