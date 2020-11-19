<?php

declare(strict_types=1);

namespace App\Routing;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @property ContainerInterface $container
 */
trait ReturnToAwareControllerTrait
{
    protected function redirectOrReturn(string $url, int $status = 302): RedirectResponse
    {
        if ($returnTo = $this->container->get('request_stack')->getCurrentRequest()->query->get('returnTo')) {
            return new RedirectResponse($returnTo, $status);
        }

        return new RedirectResponse($url, $status);
    }

    protected function redirectToRouteOrReturn(string $route, array $parameters = [], int $status = 302): RedirectResponse
    {
        return $this->redirectOrReturn($this->container->get('router')->generate($route, $parameters), $status);
    }
}
