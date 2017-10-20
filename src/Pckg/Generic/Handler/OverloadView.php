<?php namespace Pckg\Generic\Handler;

use Pckg\Framework\View\Twig;

class OverloadView
{

    public function handle(Twig $twig, $view)
    {
        $parts = collect(explode('/', $view));
        $controller = $parts->slice(0, 2)->implode('\\');
        $subview = $parts->slice(3)->implode('\\');

        foreach (config('pckg.generic.templates') as $ctrl => $views) {
            if (strpos($ctrl, $controller) !== 0) {
                continue;
            }

            foreach ($views as $view => $tpls) {
                if (in_array($controller . ':' . $subview, $tpls)) {
                    // exact match was found, allowed
                    break 2;
                }

                $foundSimilar = false;
                foreach ($tpls as $tpl) {
                    $tpl = str_replace('/', '\\', $tpl);
                    if (strpos($tpl, $controller . ':' . $subview) === 0) {
                        $foundSimilar = true;
                        break;
                    }
                }

                if (!$foundSimilar) {
                    continue;
                }

                // set to new view
                $twig->setFile(str_replace(':', '/View/', end($tpls)));
                break 2;
            }
        }
    }

}