<?php

namespace Acme\BackendBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class BackendBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
