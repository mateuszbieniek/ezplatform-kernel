<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\ProxyFactory;

use Closure;
use ProxyManager\Proxy\VirtualProxyInterface;

/**
 * @internal
 */
interface ProxyGeneratorInterface
{
    public function createProxy(string $className, Closure $initializer, array $proxyOptions = []): VirtualProxyInterface;

    public function warmUp(iterable $classes): void;
}
