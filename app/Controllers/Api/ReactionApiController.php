<?php

require_once ROOT_PATH . '/app/Controllers/Api/ApiController.php';
require_once ROOT_PATH . '/app/Controllers/Publications/ReactionController.php';

final class ReactionApiController extends ApiController
{
    public function toggle(): never
    {
        $this->requireMethod('POST');
        (new ReactionController())->toggle();
        exit;
    }
}
