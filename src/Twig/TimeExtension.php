<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TimeExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('actualTime', [$this,'getTime'])
        ];
    }

    public function getTime()
    {
        return time();
    }
}
