<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\CheeseListing;
use App\Exception\CheeseListingNotFoundException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class CheeseListingManager implements EventSubscriberInterface
{
    private $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['checkCheeselistingisPublished', EventPriorities::PRE_VALIDATE],
        ];
    }

    public function checkCheeselistingisPublished(GetResponseForControllerResultEvent $event): void
    {
        $cheeseListing = $event->getControllerResult();

        if (!$cheeseListing instanceof CheeseListing) {
            return;
        }

        $isAdmin = $this->authorizationChecker->isGranted('ROLE_ADMIN');
        if (!$cheeseListing->getIsPublished() && $isAdmin === false) {
            // Using internal codes for a better understanding of what's going on.
            throw new CheeseListingNotFoundException(sprintf('The cheeseListing "%s" does not exist.', $cheeseListing->getId()));
        }
    }
}
