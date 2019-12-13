<?php
/**
 * File Controller
 *
 * @date 07 June 2019
 * @version 1.0
 * @copyright Fiand T <fiand96@yahoo.com>
 * @author Fiand T (https://github.com/fiand1996)
 */
class FileController extends Controller
{
    /**
     * Index
     */
    public function index()
    {
        $Route = $this->getVariable("Route");
        $File = Controller::model("File", $Route->params->file);

        if (!$File->isAvailable()) {
           show_404();
        }

        try {
            $fileId = Defuse\Crypto\Crypto::encrypt(
                $File->get("file_id"), 
                \Defuse\Crypto\Key::loadFromAsciiSafeString(CRYPTO_KEY)
            );
        } catch (Exception $e) {
            $this->response->message = lang("Encryption error");
            $this->jsonecho();
        }

        $this->setVariable("File", $File)
             ->setVariable("fileId", $fileId)
             ->setVariable("pageTitle", lang("Download") ." ". $File->get("file_name"));

        if (Input::post("action") == "download") {
            $this->download();
        }

        $this->view("file", "site");
    }

    private function download()
    {
        $this->response->result = 0;
        $AuthUser = $this->getVariable("AuthUser");

        if (!$AuthUser) {
            $this->response->message = lang("Unauthorized");
            $this->jsonecho();
        }
        
        $required_fields = ["id"];

        foreach ($required_fields as $field) {
            if (!Input::post($field)) {
                $this->response->message = lang("Missing some of required data.");
                $this->jsonecho();
            }
        }

        try {
            $fileId = \Defuse\Crypto\Crypto::decrypt(
                Input::post("id"),
                \Defuse\Crypto\Key::loadFromAsciiSafeString(CRYPTO_KEY)
            );
        } catch (Exception $e) {
            $this->response->message = lang("Encryption error");
            $this->jsonecho();
        }

        $Google = $this->getVariable("Google");
        $File = $this->getVariable("File");

        if ($AuthUser->get("id") == $File->get("user_id")) {
            $this->response->result = 1;
            $this->response->redirect = $File->get("file_contentLink");
            $this->jsonecho();
        }

        try {
            $file = $Google->driveMakeCopy($fileId);
        } catch (Exception $e) {
            $this->response->message = getError($e, lang("Failed to download file"));
            $this->response->notice = true;
            $this->jsonecho();
        }
        
        $File->set("download_count", $File->get("download_count") + 1)
             ->save();

        $this->response->result = 1;
        $this->response->redirect = $file->webContentLink;
        $this->jsonecho();
    }
}
