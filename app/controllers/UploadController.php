<?php
/**
 * Upload Controller
 *
 * @date 07 June 2019
 * @version 1.0
 * @copyright Fiand T <fiand96@yahoo.com>
 * @author Fiand T (https://github.com/fiand1996)
 */
class UploadController extends Controller
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

        $this->setVariable("pageTitle", lang("Upload Files"));

        if (Input::post("action") == "upload") {
            $this->upload();
        }

        $this->view("upload", "app");
    }

    private function upload()
    {
        $this->response->result = 0;
        
        $required_fields = ["id"];

        foreach ($required_fields as $field) {
            if (!Input::post($field)) {
                $this->response->message = lang("Missing some of required data.");
                $this->jsonecho();
            }
        }

        $Google = $this->getVariable("Google");
        $AuthUser = $this->getVariable("AuthUser");

        try {
            $file = $Google->driveMakeCopy(Input::post("id"));
        } catch (Exception $e) {
            $this->response->message = getError($e, lang("Failed to copy file"));
            $this->response->notice = true;
            $this->jsonecho();
        }

        $Hashids = new Hashids\Hashids($file->id, 20);

        $Shared = Controller::model("Shared", Input::post("id"));
        $slug = $Hashids->encode(1);
        $thumnail = $file->hasThumbnail ? $file->thumbnailLink : null;
        
        $Shared->set("user_id", $AuthUser->get("id"))
               ->set("file_id", $file->id)
               ->set("file_name", $file->name)
               ->set("file_size", $file->size)
               ->set("file_mimeType", $file->mimeType)
               ->set("file_thumbnail", $thumnail)
               ->set("file_viewLink", $file->webViewLink)
               ->set("file_contentLink", $file->webContentLink)
               ->set("slug", $slug)
               ->save();

        $this->response->result = 1;
        $this->response->name = $file->name;
        $this->response->alias = APPURL . "/file/" . $slug;
        $this->jsonecho();
    }
}
