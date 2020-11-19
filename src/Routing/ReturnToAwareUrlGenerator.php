<?php

declare(strict_types=1);

namespace App\Routing;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ReturnToAwareUrlGenerator
{
    private RequestStack $requestStack;
    private UrlGeneratorInterface $generator;

    public function __construct(RequestStack $requestStack, UrlGeneratorInterface $generator)
    {
        $this->requestStack = $requestStack;
        $this->generator = $generator;
    }

    public function generate(string $name, array $parameters = [], int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): string
    {
        if ($returnTo = $this->requestStack->getCurrentRequest()->query->get('returnTo')) {
            return $returnTo;
        }

        return $this->generator->generate($name, $parameters, $referenceType);
    }

    public function generateWithReturnTo(string $name, array $parameters = [], int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): string
    {
        $request = $this->requestStack->getCurrentRequest();

        $parameters = array_merge_recursive($parameters, ['returnTo' => $request->query->get('returnTo') ?? $request->getUri()]);

        return $this->generator->generate($name, $parameters, $referenceType);
    }
}
