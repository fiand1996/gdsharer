<?php
/**
 * Bulklink Controller
 *
 * @date 07 June 2019
 * @version 1.0
 * @copyright Fiand T <fiand96@yahoo.com>
 * @author Fiand T (https://github.com/fiand1996)
 */
class BulklinkController extends Controller
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
        
        $this->setVariable("pageTitle", lang("Bulk Link"));

        if (Input::post("action") == "bulklink") {
            $this->bulklink();
        }

        $this->view("bulklink", "app");
    }

    private function bulklink()
    {
        $this->response->result = 0;
        $this->response->reset_fields = [
            "url"
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

        try {
            $file = $Google->driveMakeCopy($fileId);
        } catch (Exception $e) {
            $this->response->message = getError($e, lang("Failed to copy file"));
            $this->response->notice = true;
            $this->jsonecho();
        }

        $Hashids = new Hashids\Hashids($file->id, 20);

        $Shared = Controller::model("Shared", $file->id);
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

        $link = APPURL . '/file/' . $slug;
        $message = '<div>Sharerable link for '.$file->name.'</div><br>
                    <div class="input-group">
                        <input type="text" id="'.$slug.'" class="form-control" value="'.$link.'">
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-copy btn-default btn-flat" data-clipboard-target="#'.$slug.'">
                                <i class="fa fa-clone"></i>
                            </button>
                            <a href="'.$link.'" target="_blank" class="btn btn-default btn-flat">
                                <i class="fa fa-link"></i>
                            </a>
                        </span>
                    </div>';
                    
        $this->response->result = 1;
        $this->response->message = $message;
        $this->response->notice = true;
        $this->jsonecho();
    }
}
