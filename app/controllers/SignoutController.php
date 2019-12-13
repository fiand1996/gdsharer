<?php
/**
 * Signout Controller
 */
class SignoutController extends Controller
{
    /**
     * Process
     */
    public function index()
    {
        $this->logout();
    }

    /**
     * Logout
     * @return void
     */
    private function logout()
    {
        $AuthUser = $this->getVariable("AuthUser");
        $Google = $this->getVariable("Google");
        
        $Google->logout();

        Cookie::delete("sessid");
        Cookie::delete("sessrmm");

        // Fire user.signout event
        Event::trigger("user.signout", $AuthUser);

        header("Location: ".APPURL);
        exit;
    }
}
