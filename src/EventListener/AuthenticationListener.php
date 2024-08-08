<?php

declare(strict_types=1);

namespace ACSEO\SyliusAdminTrackerPlugin\EventListener;

use App\Shop\Entity\User\AdminUser;
use ACSEO\SyliusAdminTrackerPlugin\Entity\UserAction;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sylius\Bundle\ApiBundle\Provider\PathPrefixes;
use Sylius\Component\Core\Model\AdminUserInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;

final class AuthenticationListener
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
        private RepositoryInterface $adminUserRepository,
    ) {
    }

    public function onLoginFailure(LoginFailureEvent $event): void
    {
        if (!$this->isAdminFirewallName($event->getFirewallName())) {
            return;
        }

        $request = $event->getRequest();
        $user = $this->getAdminUser($request->get('_username'));
        if (!$user instanceof AdminUser) {
            return;
        }

        $this->addAuthenticationAction($user, 'Login failure "'.$event->getException()->getMessage().'"');
    }

    public function onLoginSuccess(AuthenticationSuccessEvent $event): void
    {
        $authenticationToken = $event->getAuthenticationToken();
        if (!$this->isAdminFirewall($authenticationToken->getRoleNames())) {
            return;
        }

        $user = $authenticationToken->getUser();
        if (!$user instanceof AdminUser) {
            return;
        }

        $this->addAuthenticationAction($user, 'Login success');
    }

    public function onLogout(LogoutEvent $event): void
    {
        $token = $event->getToken();
        if (null === $token) {
            return;
        }

        if (!$this->isAdminFirewall($token->getRoleNames())) {
            return;
        }

        $user = $event->getToken()->getUser();
        if (!$user instanceof AdminUser) {
            return;
        }

        $this->addAuthenticationAction($user, 'Logout');
    }

    public function addAuthenticationAction(AdminUser $user, string $action): void
    {
        $userAction = new UserAction();
        $userAction->setUser($user);
        $userAction->setAction($action);
        $userAction->setCreatedAt(new \DateTime());

        $this->entityManager->persist($userAction);
        $this->entityManager->flush();

        $this->logger->info($userAction->getAction());
    }

    private function isAdminFirewall(array $roleNames): bool
    {
        return \in_array(AdminUserInterface::DEFAULT_ADMIN_ROLE, $roleNames, true);
    }

    private function isAdminFirewallName(string $firewallName): bool
    {
        return PathPrefixes::ADMIN_PREFIX === $firewallName;
    }

    private function getAdminUser(?string $username): ?AdminUser
    {
        if (null === $username) {
            return null;
        }

        $user = $this->adminUserRepository->findOneBy(['username' => $username]);

        if (!$user instanceof AdminUser) {
            return null;
        }

        return $user;
    }
}
