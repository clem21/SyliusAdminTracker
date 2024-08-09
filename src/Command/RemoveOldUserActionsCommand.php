<?php

declare(strict_types=1);

namespace ACSEO\SyliusAdminTrackerPlugin\Command;

use ACSEO\SyliusAdminTrackerPlugin\Entity\UserAction;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveOldUserActionsCommand extends Command
{
    protected static $defaultName = 'sylius:remove-backups';

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Removes saved backups older than 1 year and deletes all traces.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repository = $this->entityManager->getRepository(UserAction::class);
        $oldData = $repository->createQueryBuilder('e')
            ->where('e.createdAt < :oneYearAgo')
            ->setParameter('oneYearAgo', new \DateTime('-1 year'))
            ->getQuery()
            ->getResult()
        ;

        foreach ($oldData as $data) {
            $this->entityManager->remove($data);
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
