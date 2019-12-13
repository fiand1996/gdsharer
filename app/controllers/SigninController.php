<?php
/**
 * Signin Controller
 *
 * @date 07 June 2019
 * @version 1.0
 * @copyright Fiand T <fiand96@yahoo.com>
 * @author Fiand T (https://github.com/fiand1996)
 */
class SigninController extends Controller
{
    /**
     * Index
     */
    public function index()
    {
        $AuthUser = $this->getVariable("AuthUser");

        if ($AuthUser) {
            header("Location: " . APPURL . "/dashboard");
            exit;
        }

        if (Input::get("redirect")) {
            Session::set("redirect", Input::get("redirect"));
        }

        $Google = $this->getVariable("Google");

        if (Input::get("code")) {
            try {
                $Google->authenticate(Input::get("code"));
            } catch (Exception $e) {
                setFlashMessage("error", $e->getMessage());
                header("Location: " . APPURL);
                exit;
            }

            try {
                $user = $Google->getUserInfo();
            } catch (Exception $e) {
                setFlashMessage("error", $e->getMessage());
                header("Location: " . APPURL);
                exit;
            }

            if (!isset($user['email'])) {
                setFlashMessage("error", "Connection failed, can't find your email");
                header("Location: " . APPURL);
                exit;
            }

            $User = Controller::model("User", $user['id']);
            $accessToken = $Google->getAccessToken();

            if ($User->isAvailable()) {
                if ($User->get("is_active") == 0) {
                    setFlashMessage("error", "Connection failed, your account not active or blocked");
                    header("Location: " . APPURL);
                    exit;
                }

                $User->set("token", json_encode($accessToken))
                     ->save();
            } else {
                $User->set("id", $user['id'])
                     ->set("account_type", "member")
                     ->set("email", strtolower($user['email']))
                     ->set("password", password_hash(readableRandomString(10), PASSWORD_DEFAULT))
                     ->set("firstname", $user['givenName'])
                     ->set("lastname", $user['familyName'])
                     ->set("token", json_encode($accessToken))
                     ->set("picture", $user['picture'])
                     ->set("is_active", 1)
                     ->save();
            }

            $User = Controller::model("User", $user['id']);

            $hash = $User->get("id") . "." . md5($User->get("password"));
            
            Cookie::set("sessid", $hash, 0);
            Cookie::set("sessrmm", "1", time() - 30 * 86400);

            $url = APPURL . "/dashboard";

            if (Session::exists("redirect")) {
                $url = urldecode(Session::get("redirect"));
                Session::delete("redirect");
            }
                    
            header("Location: " . $url);
            exit;
        } else if (Input::get("error")) {
            setFlashMessage("error", Input::get("error"));
            header("Location: " . APPURL);
            exit;
        } else {
            header("Location: " . $Google->getLoginUrl());
            exit;
        }
    }
}
