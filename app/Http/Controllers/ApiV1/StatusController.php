<?php

namespace App\Http\Controllers\ApiV1;

use Illuminate\Http\Request;

class StatusController extends BaseJsonController
{
    public function serverStatus(Request $request)
    {
        return $this->sendResponse(__("The server is working properly."));
    }
}
