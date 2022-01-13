<?php declare(strict_types=1);

namespace Networkteam\ConsoleCommands\Command;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Plugin\Command\Lifecycle\PluginUpdateCommand;
use Shopware\Core\Framework\Plugin\PluginCollection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PluginUpdateAllCommand extends Command
{
    protected static $defaultName = 'networkteam:plugin:updateall';

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
            ->setDescription('Update all plugins (and install + activate')
            ->addOption('clear-cache', null, InputOption::VALUE_NONE, 'Clear cache, given something changes');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var PluginCollection $plugins */
        $plugins = $this->pluginRepo->search(new Criteria(), Context::createDefaultContext())->getEntities();

        $clearCache = false;
        foreach ($plugins as $plugin) {
            if (!$plugin->getInstalledAt()) {
                $output->writeln('Install plugin ' . $plugin->getName());
                $command = $this->getApplication()->find('plugin:install');
                $command->run(
                    new ArrayInput(['plugins' => [$plugin->getName()]]),
                    $output);
                $clearCache = true;
            }
        }
        foreach ($plugins as $plugin) {
            if (!$plugin->getActive()) {
                $output->writeln('Activate plugin ' . $plugin->getName());
                $command = $this->getApplication()->find('plugin:activate');
                $command->run(
                    new ArrayInput(['plugins' => [$plugin->getName()]]),
                    $output);
                $clearCache = true;
            }
        }
        foreach ($plugins as $plugin) {
            if ($plugin->getUpgradeVersion()) {
                $output->writeln('Update plugin ' . $plugin->getName());
                $command = $this->getApplication()->find('plugin:update');
                $command->run(
                    new ArrayInput(['plugins' => [$plugin->getName()]]),
                    $output);
                $clearCache = true;
            }
        }

        if ($input->getOption('clear-cache') && $clearCache) {
            $command = $this->getApplication()->find('cache:clear');
            $command->run(new ArrayInput([]), $output);
        }

        return self::SUCCESS;
    }
}
