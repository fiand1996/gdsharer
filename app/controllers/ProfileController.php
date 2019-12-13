<?php
/**
 * Profile Controller
 *
 * @date 07 June 2019
 * @version 1.0
 * @copyright Fiand T <fiand96@yahoo.com>
 * @author Fiand T (https://github.com/fiand1996)
 */
class ProfileController extends Controller
{
    /**
     * Index
     */
    public function index()
    {
        $AuthUser = $this->getVariable("AuthUser");

        if (!$AuthUser) {
            header("Location: " . APPURL);
            exit;
        }

        $pageTitle = "{$AuthUser->get("firstname")} {$AuthUser->get("lastname")}";

        $this->setVariable("timezones", getTimezones())
             ->setVariable("pageTitle", $pageTitle);

        if (Input::post("action") == "save") {
            $this->save();
        } elseif (Input::post("action") == "upload") {
            $this->upload();
        }

        $this->view("profile", "app");
    }
    
    private function save()
    {
        $this->response->result = 0;

        $AuthUser = $this->getVariable("AuthUser");

        // Check required fields
        $required_fields = ["firstname", "lastname"];

        foreach ($required_fields as $field) {
            if (!Input::post($field)) {
                $this->response->message = lang("Missing some of required data.");
                $this->jsonecho();
            }
        }

        $AuthUser->set("firstname", Input::post("firstname"))
                 ->set("lastname", Input::post("lastname"));

        $timezone = Input::post("timezone");
        if (!in_array($timezone, DateTimeZone::listIdentifiers())) {
            $timezone = "UTC";
        }

        $valid_date_formats = [
            "Y-m-d", "d-m-Y", "d/m/Y", "m/d/Y",
            "d F Y", "F d, Y", "d M, Y", "M d, Y",
        ];

        $dateformat = Input::post("date_format");
        if (!in_array($dateformat, $valid_date_formats)) {
            $dateformat = $valid_date_formats[0];
        }

        $timeformat = Input::post("time_format") == "24" ? "24" : "12";

        $AuthUser->set("date_format", $dateformat)
                 ->set("time_format", $timeformat)
                 ->set("timezone", $timezone);

        $AuthUser->save();

        $this->response->result = 1;
        $this->response->message = lang("Changes saved!");
        $this->jsonecho();
    }

    private function upload()
    {
        $this->response->result = 0;

        $AuthUser = $this->getVariable("AuthUser");

        $allowedExtension = ['png', 'jpg'];
        $imageName = $_FILES['image']['name'];
        $imageSize	= $_FILES['image']['size'];
        $imageTemp = $_FILES['image']['tmp_name'];
        $ext = explode('.', $imageName);
        $imageExtension = end($ext);
        $Hashids = new Hashids\Hashids(readableRandomString(10), 50);
        $newname = $Hashids->encode(1) . ".{$imageExtension}";
        $imagePath = ROOTPATH . "/uploads/users/{$newname}"; 

        if (! in_array($imageExtension, $allowedExtension)) {
            $this->response->message = lang("This file extension is not allowed. Please upload a JPEG or PNG file");
            $this->jsonecho();
        }

        if ($imageSize > 2000000) {
            $this->response->message = lang("This file is more than 2MB. Sorry, it has to be less than or equal to 2MB");
            $this->jsonecho();
        }

        if (move_uploaded_file($imageTemp, $imagePath)) {

            $oldImage = str_replace(APPURL, ROOTPATH, $AuthUser->get("picture"));
            if (file_exists($oldImage)) {
                @unlink($oldImage);
            }

            $AuthUser->set("picture", APPURL . "/uploads/users/{$newname}")
                     ->save();
            
            $ImageResize = new \Gumlet\ImageResize($imagePath);
            $ImageResize->resize(100, 100);
            $ImageResize->save($imagePath);

            $this->response->result = 1;
            $this->response->redirect = APPURL . "/profile";
            $this->jsonecho();
        }

        $this->response->message = lang("An error occurred somewhere. Try again or contact the admin");
        $this->jsonecho();
    }
}
