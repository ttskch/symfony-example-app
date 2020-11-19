<?php

declare(strict_types=1);

namespace App\Twig;

use App\Routing\ReturnToAwareUrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RoutingExtension extends AbstractExtension
{
    private ReturnToAwareUrlGenerator $returnToAwareUrlGenerator;

    public function __construct(ReturnToAwareUrlGenerator $returnToAwareUrlGenerator)
    {
        $this->returnToAwareUrlGenerator = $returnToAwareUrlGenerator;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('pathOrReturnTo', [$this, 'pathOrReturnTo']),
            new TwigFunction('pathWithReturnTo', [$this, 'pathWithReturnTo']),
        ];
    }

    /**
     * @see \Symfony\Bridge\Twig\Extension\RoutingExtension::getPath()
     */
    public function pathOrReturnTo(string $name, array $parameters = [], bool $relative = false)
    {
        return $this->returnToAwareUrlGenerator->generate($name, $parameters, $relative ? UrlGeneratorInterface::RELATIVE_PATH : UrlGeneratorInterface::ABSOLUTE_PATH);
    }

    public function pathWithReturnTo(string $name, array $parameters = [], bool $relative = false)
    {
        return $this->returnToAwareUrlGenerator->generateWithReturnTo($name, $parameters, $relative ? UrlGeneratorInterface::RELATIVE_PATH : UrlGeneratorInterface::ABSOLUTE_PATH);
    }
}
