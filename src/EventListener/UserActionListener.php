<?php

declare(strict_types=1);

namespace ACSEO\SyliusAdminTrackerPlugin\EventListener;

use ACSEO\SyliusAdminTrackerPlugin\Entity\UserAction;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\AdminUserInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserActionListener implements EventSubscriber
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security,
        private LoggerInterface $logger,
    ) {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
            Events::postUpdate,
            Events::postRemove,
        ];
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $shortClassName = (new \ReflectionClass($args->getObject()))->getShortName();
        if (!\in_array($shortClassName, ['Product', 'Customer', 'Promotion', 'Refund', 'User', 'ProductOption', 'Taxon', 'ProductAssociation', 'ProductAttribute'], true)) {
            return;
        }
        $this->addUserAction($args->getObject(), [], 'Add');
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $this->addUserAction($args->getObject(), $this->entityToArray($this->getChangeSet($args)), 'Update');
    }

    public function postRemove(LifecycleEventArgs $args): void
    {
        $this->addUserAction($args->getObject(), [], 'Remove');
    }

    public function addUserAction(object $object, array $oldValue, string $action): void
    {
        $user = $this->getCurrentAdminUser();
        if (!$user instanceof AdminUserInterface) {
            return;
        }

        if (
            (\count($oldValue) === 2 && \array_key_exists('lastLogin', $oldValue) && \array_key_exists('updatedAt', $oldValue))
            || (\count($oldValue) === 2 && \array_key_exists('loginAttempt', $oldValue) && \array_key_exists('updatedAt', $oldValue))
            || (\count($oldValue) === 1 && \array_key_exists('updatedAt', $oldValue))
        ) {
            return;
        }

        $userAction = new UserAction();
        $userAction->setUser($user);
        $userAction->setAction($this->getClassName($object, $action));
        $userAction->setCreatedAt(new \DateTime());
        $userAction->setOldValue($oldValue);

        $this->entityManager->persist($userAction);
        $this->entityManager->flush();

        $this->logger->info($userAction->getAction(), ['values' => json_encode($userAction->getOldValue())]);
    }

    private function getCurrentAdminUser(): ?UserInterface
    {
        return $this->security->getUser();
    }

    public function getChangeSet(LifecycleEventArgs $args): array
    {
        $entity = $args->getObject();
        $entityManager = $args->getObjectManager();

        return $entityManager->getUnitOfWork()->getEntityChangeSet($entity);
    }

    private function getClassName(object $object, string $prefix): string
    {
        $suffix = '';
        if (method_exists($object, 'getUsernameCanonical')) {
            $suffix = 'with username : '.$object->getUsernameCanonical();
        } elseif (method_exists($object, 'getCode')) {
            $suffix = 'with code : '.$object->getCode();
        } elseif (method_exists($object, 'getName')) {
            $suffix = 'with name : '.$object->getName();
        } elseif (method_exists($object, 'getNumber')) {
            $suffix = 'with number : '.$object->getNumber();
        } elseif (method_exists($object, 'getId')) {
            $suffix = 'with id : '.$object->getId();
        }
        $shortClassName = (new \ReflectionClass($object))->getShortName();

        return sprintf('%s %s %s', $prefix, $shortClassName, $suffix);
    }

    private function entityToArray(array $values): array
    {
        $array = [];
        foreach ($values as $key => $value) {
            if (!\is_object($value)) {
                $array[$key] = $value;

                continue;
            }

            $reflector = new \ReflectionObject($value);
            $nodes = $reflector->getProperties();
            $out = [];
            foreach ($nodes as $node) {
                $nod = $reflector->getProperty($node->getName());
                $nod->setAccessible(true);
                $out[$node->getName()] = $nod->getValue($value);
            }
            $array[$key] = $out;
        }

        return $array;
    }
}
