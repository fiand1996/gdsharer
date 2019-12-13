<?php
/**
 * Copy Controller
 *
 * @date 07 June 2019
 * @version 1.0
 * @copyright Fiand T <fiand96@yahoo.com>
 * @author Fiand T (https://github.com/fiand1996)
 */
class CopyController extends Controller
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

        $this->setVariable("pageTitle", lang("Copy File"));
        
        if (Input::post("action") == "copy") {
            $this->copy();
        }

        $this->view("copy", "app");
    }

    private function copy()
    {
        $this->response->result = 0;
        $this->response->reset_fields = [
            "name", "url"
        ];

        $required_fields = ["url"];

        foreach ($required_fields as $field) {
            if (!Input::post($field)) {
                $this->response->message = lang("Missing some of required data.");
                $this->jsonecho();
            }
        }

        $fileId = getDriveId(Input::post("url"));

        if (!$fileId) {
            $this->response->message = lang("Url invalid");
            $this->jsonecho();
        }

        $Google = $this->getVariable("Google");
        $AuthUser = $this->getVariable("AuthUser");
        $name = Input::post("name") ? Input::post("name") : null;

        try {
            $file = $Google->driveMakeCopy($fileId, $name, false);
        } catch (Exception $e) {
            $this->response->message = getError($e, lang("Failed to copy file"));
            $this->response->notice = true;
            $this->jsonecho();
        }

        $this->response->result = 1;
        $this->response->message = lang("%s has been copied to your drive!", $file->name);
        $this->response->notice = true;
        $this->jsonecho();
    }
}
