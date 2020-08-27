<?php

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use ApiPlatform\Core\Bridge\Doctrine\Common\DataPersister;
use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserDataPersister implements ContextAwareDataPersisterInterface
{
    private $doctrineDataPersister;
    private $userPasswordEncoder;

    public function __construct(DataPersister $doctrineDataPersister, UserPasswordEncoderInterface $userPasswordEncoder)
    {
        $this->doctrineDataPersister = $doctrineDataPersister;
        $this->userPasswordEncoder = $userPasswordEncoder;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof User;
    }

    /**
     * @param User $data
     */
    public function persist($data, array $context = [])
    {
        if ($data->getPlainPassword()) {
            $data->setPassword(
                $this->userPasswordEncoder->encodePassword($data, $data->getPlainPassword())
            );
            $data->eraseCredentials();
        }

        return $this->doctrineDataPersister->persist($data, $context);
    }

    public function remove($data, array $context = [])
    {
        $this->doctrineDataPersister->remove($data, $context);
    }
}
