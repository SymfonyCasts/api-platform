<?php

namespace App\Dto;

use App\Entity\CheeseListing;
use App\Entity\User;
use Carbon\Carbon;
use Symfony\Component\Serializer\Annotation\Groups;

class CheeseListingOutput
{
    /**
     * The title of this listing
     *
     * @Groups({"cheese:read", "user:read"})
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
     * @Groups({"cheese:read", "user:read"})
     */
    public $price;

    public $createdAt;

    /**
     * @var User
     * @Groups({"cheese:read"})
     */
    public $owner;

    public static function createFromEntity(CheeseListing $cheeseListing): self
    {
        $output = new CheeseListingOutput();
        $output->title = $cheeseListing->getTitle();
        $output->description = $cheeseListing->getDescription();
        $output->price = $cheeseListing->getPrice();
        $output->owner = $cheeseListing->getOwner();
        $output->createdAt = $cheeseListing->getCreatedAt();

        return $output;
    }

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
        return Carbon::instance($this->createdAt)->diffForHumans();
    }
}
