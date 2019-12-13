<?php
/**
 * Mydrive Controller
 *
 * @date 07 June 2019
 * @version 1.0
 * @copyright Fiand T <fiand96@yahoo.com>
 * @author Fiand T (https://github.com/fiand1996)
 */
class MydriveController extends Controller
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

        $Google = $this->getVariable("Google");

        if (Input::server("REQUEST_METHOD") == "GET") {
            \GDShareController::updateDrive($Google, $AuthUser);
        }
        
        $pageSize = 12;

        $Mydrives = Controller::model("Mydrives");
        $Mydrives->search(Input::get("q"))
                 ->setPageSize($pageSize)
                 ->setPage(Input::get("page"))
                 ->where("user_id", "=", $AuthUser->get("id"))
                 ->orderBy("created", "DESC")
                 ->fetchData();

        $Pagination = new DawPhpPagination\Pagination([
            "pp" => $pageSize,
            "css_class_p" => "pagination no-margin pull-right",
        ]);

        $Pagination->paginate($Mydrives->getTotalCount());

        $this->setVariable("Mydrives", $Mydrives)
             ->setVariable("Pagination", $Pagination)
             ->setVariable("pageTitle", lang("My Drive"));

        if (Input::post("action") == "share") {
            $this->share();
        } else if (Input::post("action") == "remove") {
            $this->remove();
        }

        $this->view("mydrive", "app");
    }

    private function remove()
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

        $Mydrive = Controller::model("Mydrive", Input::post("id"));

        if (!$Mydrive->isAvailable() || 
            $Mydrive->get("user_id") != $AuthUser->get("id")) 
        {
            $this->response->message = lang("Unauthorized");
            $this->response->notice = true;
            $this->jsonecho();
        }

        try {
            $Google->driveDelete(Input::post("id"));
        } catch (Exception $e) {
            $this->response->message = getError($e, lang("Failed to delete file"));
            $this->response->notice = true;
            $this->jsonecho();
        }
        
        $Shared = Controller::model("Shared", Input::post("id"));
        $Shared->delete();
        $Mydrive->delete();

        $this->response->result = 1;
        $this->jsonecho();
    }

    private function share()
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

        $Mydrive = Controller::model("Mydrive", Input::post("id"));

        if (!$Mydrive->isAvailable() || 
            $Mydrive->get("user_id") != $AuthUser->get("id")) 
        {
            $this->response->message = lang("Unauthorized");
            $this->response->notice = true;
            $this->jsonecho();
        }

        try {
            $file = $Google->driveMakeCopy(Input::post("id"));
        } catch (Exception $e) {
            $this->response->message = getError($e, lang("Failed to share file"));
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
        $this->jsonecho();
    }
}
