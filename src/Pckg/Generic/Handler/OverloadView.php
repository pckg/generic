<?php namespace Pckg\Generic\Handler;

use Pckg\Framework\View\Twig;

class OverloadView
{

    public function handle(Twig $twig, $view)
    {
        if (!$view) {
            return;
        }

        $view = str_replace(['\\', '/View/'], ['/', ':'], $view);
        $parts = collect(explode('/', $view));
        $subcontroller = $parts->slice(0, 2)->implode('/');

        $controllerPart = explode(':', $view)[0] ?? null;
        $viewPart = explode(':', $view)[1] ?? null;

        foreach (config('pckg.generic.templates') as $ctrl => $views) {
            $ctrl = str_replace('\\', '/', $ctrl);

            if (strpos($ctrl, $controllerPart) !== 0) {
                /**
                 * View's controller part is not part of this $ctrl, skip.
                 */
                continue;
            }

            foreach ($views as $viewKey => $tpls) {
                if (in_array($subcontroller . ':' . $viewPart, $tpls)) {
                    /**
                     * Exact match was found, view is allowed.
                     */
                    break 2;
                }

                if (strpos($view, $subcontroller . '/' . $viewKey) !== 0) {
                    /**
                     * We're not checking for correct action.
                     */
                    continue;
                }

                $similar = null;
                foreach ($tpls as $tpl) {
                    if (strpos($tpl, $subcontroller . '/' . $viewKey) !== 0) {
                        /**
                         * ?
                         */
                        continue;
                    }

                    /**
                     * Similar view was found for current action.
                     */
                    $similar = $tpl;
                    break;
                }

                /**
                 * Set to last defined view.
                 */
                if (!$similar) {
                    message('No similar view found for ' . $view);
                    $similar = end($tpls);
                } else {
                }
                message('Overloading ' . $view . ' to ' . $similar);

                // set to new view
                $file = str_replace(':', '/View/', $similar);
                $twig->setFile($file);
                break 2;
            }
        }
    }

}