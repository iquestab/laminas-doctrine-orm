<?php
declare(strict_types=1);

namespace Skar\LaminasDoctrineORM\Command;

use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Doctrine\ORM\EntityManager;
use ErrorException;
use Interop\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\HelperSet;

trait MigrationsCommandTrait {
	/**
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * MigrationsDiff constructor.
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container) {
		$this->container = $container;
		parent::__construct();
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 */
	public function initialize(InputInterface $input, OutputInterface $output) : void {
		/** @var $em EntityManager */
		$em = $this->container->get(EntityManager::class);
		/** @var HelperSet $helperSet */
		$helperSet = $this->getApplication()->getHelperSet();
		$helperSet->set(new ConnectionHelper($em->getConnection()), 'connection');
		$helperSet->set(new EntityManagerHelper($em), 'em');

		$this->configuration = $this->getMigrationConfiguration($input, $output);

		$config = $this->container->get('config')['doctrine']['migrations'];
		if (!is_dir($config['directory']) && !mkdir($config['directory'], 0755, true)) {
			$error = error_get_last();
			throw new ErrorException($error['message']);
		}
		$this->configuration->setMigrationsDirectory($config['directory']);

		if (!$this->configuration->getMigrationsNamespace()) {
			$this->configuration->setMigrationsNamespace($config['namespace']);
		}

		if(isset($config['table_name'])) {
			$this->configuration->setMigrationsTableName($config['table_name']);
		}

		if(isset($config['organize_migrations'])) {
			if($config['organize_migrations'] === 'year') {
				$this->configuration->setMigrationsAreOrganizedByYear(true);
			} else if($config['organize_migrations'] === 'year_and_month') {
				$this->configuration->setMigrationsAreOrganizedByYearAndMonth(true);
			}
		}

		if (!$this->configuration->getName()) {
			$this->configuration->setName($config['name']);
		}

		parent::initialize($input, $output);
	}
}
