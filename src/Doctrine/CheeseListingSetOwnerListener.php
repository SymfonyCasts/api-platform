<?php

namespace App\Doctrine;

use App\Entity\CheeseListing;

class CheeseListingSetOwnerListener
{
    public function prePersist(CheeseListing $cheeseListing)
    {
    }
}
