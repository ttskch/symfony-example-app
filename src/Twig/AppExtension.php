<?php

declare(strict_types=1);

namespace App\Twig;

use App\Security\RoleManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    private RoleManager $rm;
    private TranslatorInterface $translator;
    private RequestStack $reqestStack;

    public function __construct(RoleManager $rm, TranslatorInterface $translator, RequestStack $requestStack)
    {
        $this->rm = $rm;
        $this->translator = $translator;
        $this->reqestStack = $requestStack;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('roles', [$this, 'roles'], ['is_safe' => ['html']]),
        ];
    }

    public function getFilters()
    {
        return [
            new TwigFilter('datetime', [$this, 'datetime']),
        ];
    }

    public function roles(UserInterface $user): string
    {
        $badges = [];

        foreach ($this->rm->getReachableRoles($user) as $role) {
            $badges[] = sprintf('<span class="badge badge-secondary">%s</span>', $this->translator->trans($role));
        }

        return implode(' ', $badges);
    }

    public function datetime(?\DateTimeInterface $datetime): string
    {
        if ($this->reqestStack->getCurrentRequest()->getLocale() === 'ja') {
            $days = ['日', '月', '火', '水', '木', '金', '土'];

            return $datetime === null ? '' : sprintf($datetime->format('Y/m/d(%\s) H:i:s'), $days[(int) $datetime->format('w')]);
        }

        return $datetime === null ? '' : sprintf($datetime->format('Y/m/d D H:i:s'));
    }
}
