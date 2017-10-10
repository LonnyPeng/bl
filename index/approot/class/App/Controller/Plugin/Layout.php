<?php

namespace App\Controller\Plugin;

use Framework\Controller\Plugin\Layout as BlLayout;

class Layout extends BlLayout
{
    const LAYOUT_DEFAULT    = 'layout/default';
    const LAYOUT_UE    = 'layout/ue';

    public function __invoke($model = null)
    {
        parent::__invoke($model);

        $layout = $this->getModel();

        if ($layout) {
            if (!$layout->title) {
                $layout->title = '白领';
            }
        }
        return $this;
    }
}