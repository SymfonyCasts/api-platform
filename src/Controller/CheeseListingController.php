<?php

namespace App\Controller;

use App\Entity\CheeseListing;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class CheeseListingController extends AbstractController
{

    /**
     * @Route(
     *     name="publish_cheese_listing",
     *     path="api/market/cheeses/{id}/publish",
     *     methods={"PUT"},
     *     defaults={
     *       "_controller"="\App\Controller\CheeseListingController::publishCheeseListingToSocialMedia",
     *       "_api_resource_class"="App\Entity\CheeseListing",
     *       "_api_item_operation_name"="publishCheese"
     *     }
     *   )
     */
    public function publishCheeseListingToSocialMedia(CheeseListing $data, ObjectManager $manager): CheeseListing
    {
        if ($data->getPublished() === false) {
            $data->publish();
            $manager->persist($data);
            $manager->flush();

            // Image that we can publish a cheese advert to a social medium platform:
            // $cheeseListingService->publishToFacebook($cheeseListing);
        }
        return $data;
    }
}
