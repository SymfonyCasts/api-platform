<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\CheeseListing;
use App\Exception\CheeseListingNotFoundException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class CheeseListingManager implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['checkCheeselistingisPublished', EventPriorities::PRE_VALIDATE],
        ];
    }

    public function checkCheeselistingisPublished(GetResponseForControllerResultEvent $event): void
    {

        $cheeseListing = $event->getControllerResult();
        if (!$cheeseListing instanceof CheeseListing || !$event->getRequest()->isMethodSafe(false)) {
            return;
        }

        $isAdmin = false;
        if (!$cheeseListing->getIsPublished() && $isAdmin === false) {
            // Using internal codes for a better understanding of what's going on
            throw new CheeseListingNotFoundException(sprintf('The cheeseListing "%s" does not exist.', $cheeseListing->getId()));
        }
    }
}
