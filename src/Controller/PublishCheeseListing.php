<?php

namespace App\Controller;

use App\Entity\CheeseListing;

class PublishCheeseListing
{
    public function __invoke(CheeseListing $data): CheeseListing
    {
        if ($data->getIsPublished() === false) {
            $data->setIsPublished(true);
        }
        return $data;
    }
}
