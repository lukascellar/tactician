<?php

declare(strict_types=1);

namespace Cellar\Tactician\Middleware;

use Cellar\Tactician\Event\CommandFailedEvent;
use Cellar\Tactician\Event\CommandHandledEvent;
use Exception;
use League\Tactician\Middleware;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class EventDispatcherMiddleware implements Middleware
{
	/** @var EventDispatcherInterface */
	private $eventDispatcher;

	public function __construct(EventDispatcherInterface $eventDispatcher)
	{
		$this->eventDispatcher = $eventDispatcher;
	}

	/**
	 * @inheritDoc
	 */
	public function execute($command, callable $next)
	{
		try {
			$result = $next($command);
			$this->eventDispatcher->dispatch(CommandHandledEvent::class, new CommandHandledEvent($command));
			return $result;
		} catch (Exception $e) {
			$this->eventDispatcher->dispatch(CommandFailedEvent::class, new CommandFailedEvent($command));
			throw $e;
		}
	}
}