<?php

namespace Skar\LaminasDoctrineORM;

class Module
{
	/**
	 * @see ConfigProviderInterface::getConfig
	 */
	public function getConfig()
	{
		return include __DIR__ . '/../config/module.config.php';
	}
}
