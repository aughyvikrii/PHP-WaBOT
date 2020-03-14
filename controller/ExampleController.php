<?php if( !defined("BOT_START") ) die("Direct access is not allowed.");

/**
 * ExampleController
 * for use example
 */

class ExampleController extends Controller {
    
    /**
     * Route follow
     * When someone add this BOT
     */

    public function follow() {

        $message = "Thank you for adding this BOT";

        return $this->response($message);
    }

}