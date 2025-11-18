<?php

namespace Src\Controllers;

class HealthController extends BaseController
{
    public function show()
    {
        $this->ok([
            'status' => 'pokon',
            'time' => date('c')
        ]);
    }
}
