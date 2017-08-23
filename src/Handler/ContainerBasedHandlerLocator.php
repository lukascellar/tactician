<?php

declare(strict_types=1);

namespace Cellar\Tactician\Handler;

use League\Tactician\Exception\MissingHandlerException;
use League\Tactician\Handler\Locator\HandlerLocator;
use Nette\DI\Container;

final class ContainerBasedHandlerLocator implements HandlerLocator
{
	/** @var Container */
	private $container;

	/** @var array */
	private $commandToServiceIdMapping;

	public function __construct(Container $container, array $commandToServiceIdMapping)
	{
		$this->container = $container;
		$this->commandToServiceIdMapping = $commandToServiceIdMapping;
	}

	/**
	 * @inheritDoc
	 */
	public function getHandlerForCommand($commandName)
	{
		if (isset($this->commandToServiceIdMapping[$commandName]) === false) {
			throw MissingHandlerException::forCommand($commandName);
		}

		if ($this->container->hasService($this->commandToServiceIdMapping[$commandName]) === false) {
			throw MissingHandlerException::forCommand($commandName);
		}

		return $this->container->getService($this->commandToServiceIdMapping[$commandName]);
	}
}