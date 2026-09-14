<?php

declare (strict_types=1);
namespace SmashBalloon\YoutubeFeed\Vendor\DI;

use SmashBalloon\YoutubeFeed\Vendor\Psr\Container\ContainerExceptionInterface;
/**
 * Exception for the Container.
 */
class DependencyException extends \Exception implements ContainerExceptionInterface
{
}
