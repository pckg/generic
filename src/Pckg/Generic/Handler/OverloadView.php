<?php namespace Pckg\Generic\Handler;

use Pckg\Framework\View\Twig;

class OverloadView
{

    public function handle(Twig $twig, $view)
    {
        if (!$view) {
            return;
        }

        $parts = collect(explode('/', $view));
        $controller = $parts->slice(0, 2)->implode('\\');
        $controller2 = $parts->slice(0, 2)->implode('/');
        $subview = $parts->slice(3)->implode('/');

        foreach (config('pckg.generic.templates') as $ctrl => $views) {
            if (strpos($ctrl, $controller) !== 0) {
                continue;
            }

            foreach ($views as $v => $tpls) {
                if (in_array($controller2 . ':' . $subview, $tpls)) {
                    // exact match was found, allowed
                    break 2;
                }

                $foundSimilar = false;
                foreach ($tpls as $tpl) {
                    if (strpos($tpl, $controller2 . ':' . $subview) === 0) {
                        $foundSimilar = true;
                        break;
                    }
                }

                if (!$foundSimilar) {
                    continue;
                }

                // set to new view
                $file = str_replace(':', '/View/', end($tpls));
                $twig->setFile($file);
                break 2;
            }
        }
    }

}