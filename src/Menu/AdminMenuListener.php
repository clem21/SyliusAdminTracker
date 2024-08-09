<?php

declare(strict_types=1);

namespace ACSEO\SyliusAdminTrackerPlugin\Menu;

use Knp\Menu\ItemInterface;
use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class AdminMenuListener
{
    public function addAdminMenuItems(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        $this->userActionMenu($menu);
    }

    private function userActionMenu(ItemInterface $menu): void
    {
        $userAction = $menu
            ->addChild('user_action')
            ->setLabel('sylius.ui.bo_actions')
        ;

        $userAction
            ->addChild('user_action', ['route' => 'sylius_admin_user_action_index'])
            ->setLabel('sylius.ui.user_action')
            ->setLabelAttribute('icon', 'file alternate')
        ;
    }
}
