<?php declare(strict_types=1);

namespace Networkteam\ConsoleCommands\Command;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Plugin\PluginCollection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PluginListCommand extends Command
{
    protected static $defaultName = 'networkteam:plugin:list';

    /**
     * @var EntityRepositoryInterface
     */
    private $pluginRepo;

    public function __construct(EntityRepositoryInterface $pluginRepo)
    {
        parent::__construct();
        $this->pluginRepo = $pluginRepo;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Show a list of plugins with additional options.')
            ->addOption('updatable', null, InputOption::VALUE_NONE, 'List updatable plugins')
            ->addOption('installed', null, InputOption::VALUE_NONE, 'List installed plugins')
            ->addOption('uninstalled', null, InputOption::VALUE_NONE, 'List not installed plugins')
            ->addOption('active', null, InputOption::VALUE_NONE, 'List active plugins')
            ->addOption('inactive', null, InputOption::VALUE_NONE, 'List inactive plugins');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var PluginCollection $plugins */
        $plugins = $this->pluginRepo->search(new Criteria(), Context::createDefaultContext())->getEntities();
        foreach ($plugins as $plugin) {
            if ($input->getOption('updatable') && $plugin->getUpgradeVersion()) {
                $output->writeln($plugin->getName());
            }
            if ($input->getOption('active') && $plugin->getActive()) {
                $output->writeln($plugin->getName());
            }
            if ($input->getOption('inactive') && !$plugin->getActive()) {
                $output->writeln($plugin->getName());
            }
            if ($input->getOption('installed') && $plugin->getInstalledAt()) {
                $output->writeln($plugin->getName());
            }
            if ($input->getOption('uninstalled') && !$plugin->getInstalledAt()) {
                $output->writeln($plugin->getName());
            }
        }

        return self::SUCCESS;
    }
}
