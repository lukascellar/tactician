<?php

namespace Cellar\Tactician\DI;

use Cellar\Tactician\Handler\ContainerBasedHandlerLocator;
use League\Tactician\Bernard\QueueMiddleware;
use League\Tactician\CommandBus;
use League\Tactician\Handler\CommandHandlerMiddleware;
use League\Tactician\Handler\CommandNameExtractor\ClassNameExtractor;
use League\Tactician\Handler\MethodNameInflector\HandleInflector;
use League\Tactician\Plugins\LockingMiddleware;
use Nette\DI\CompilerExtension;
use Nette\InvalidStateException;

final class TacticianExtension extends CompilerExtension
{
	const TAG_HANDLER = 'tactician.handler';

	/** @var array */
	private $defaultConfiguration = [
		'commandbus' => [
			'default' => [
				'middleware' => []
			]
		]
	];

	public function beforeCompile()
	{
		$config = $this->getConfig($this->defaultConfiguration);
		$builder = $this->getContainerBuilder();

		/**
		 * MethodNameInflector
		 */
		$builder->addDefinition($this->prefix('method_name_inflector'))
			->setClass(HandleInflector::class);

		/**
		 * HandlerLocator
		 */
		$builder->addDefinition($this->prefix('handler_locator'))
			->setClass(ContainerBasedHandlerLocator::class)
			->setArguments([
				'commandToServiceIdMapping' => $this->getCommandHandlerMap()
			]);

		/**
		 * CommandNameExtractor
		 */
		$builder->addDefinition($this->prefix('class_name_extractor'))
			->setClass(ClassNameExtractor::class);

		/**
		 * LockingMiddleware
		 */
		$builder->addDefinition($this->prefix('middleware.locking'))
			->setClass(LockingMiddleware::class);

		/**
		 * CommandHandlerMiddleware
		 */
		$builder->addDefinition($this->prefix('middleware.command_handler'))
			->setClass(CommandHandlerMiddleware::class);

		/**
		 * QueueMiddleware
		 */
		if (class_exists('League\Tactician\Bernard\QueueMiddleware')) {
			$builder->addDefinition($this->prefix('middleware.queue'))
				->setClass(QueueMiddleware::class);
		}

		foreach ($config['commandbus'] as $name => $commandBusConfig) {
			$this->configureCommandBus($name, $commandBusConfig);
		}
	}

	/**
	 * @param string $name
	 * @param array $config
	 */
	private function configureCommandBus(string $name, array $config): void
	{
		$builder = $this->getContainerBuilder();
		$middleware = isset($config['middleware']) ? $config['middleware'] : [];

		$builder->addDefinition($this->prefix('commandbus' . $name))
			->setClass(CommandBus::class)
			->setArguments([
				$middleware
			]);
	}

	/**
	 * @return array
	 */
	private function getCommandHandlerMap(): array
	{
		$handlers = $this
			->getContainerBuilder()
			->findByTag(self::TAG_HANDLER);

		$commandHandlerMap = [];

		foreach ($handlers as $id => $handler) {

			if (isset($commandHandlerMap[$handler['command']])) {
				throw new InvalidStateException(sprintf('Missing command name tag for service "%s"', $id));
			}

			$commandHandlerMap[$handler['command']] = $id;
		}

		return $commandHandlerMap;
	}
}