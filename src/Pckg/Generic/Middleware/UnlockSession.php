<?php

namespace Pckg\Generic\Middleware;

use Pckg\Concept\Reflect;
use Pckg\Framework\Request;
use Pckg\Framework\Response;
use Pckg\Generic\Controller\Generic;

class UnlockSession
{

    public function execute(callable $next)
    {
        if (!isHttp() || in_array('session:keep', router()->get('tags'))) {
            return $next();
        }

        session_write_close();
        return $next();
    }
}
