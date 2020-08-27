<?php

namespace App\Dto;

use App\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

class CheeseListingInput
{
    /**
     * @Groups({"cheese:write", "user:write"})
     */
    public $title;

    /**
     * @Groups({"cheese:write", "user:write"})
     */
    public $price;

    /**
     * @var User
     * @Groups({"cheese:collection:post"})
     */
    public $owner;

    public $description;

    /**
     * The description of the cheese as raw text.
     *
     * @Groups({"cheese:write", "user:write"})
     * @SerializedName("description")
     */
    public function setTextDescription(string $description): self
    {
        $this->description = nl2br($description);

        return $this;
    }
}
