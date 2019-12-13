<?php
/**
 * Dashboard Controller
 *
 * @date 07 June 2019
 * @version 1.0
 * @copyright Fiand T <fiand96@yahoo.com>
 * @author Fiand T (https://github.com/fiand1996)
 */
class DashboardController extends Controller
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
        
        $DriveAbout = $Google->getDriveInfo();

        $Shareds = Controller::model("Shareds");
        $Shareds->where("user_id", "=", $AuthUser->get("id"))
                ->fetchData();

        $MostDownload = Controller::model("Shareds");
        $MostDownload->setPageSize(5)
                     ->setPage(Input::get("page"))
                     ->where("download_count", ">", 0)
                     ->where("user_id", "=", $AuthUser->get("id"))
                     ->orderBy("download_count","DESC")
                     ->fetchData();
        
        $LastShared = Controller::model("Shareds");
        $LastShared->setPageSize(5)
                   ->setPage(Input::get("page"))
                   ->where("user_id", "=", $AuthUser->get("id"))
                   ->orderBy("id","DESC")
                   ->fetchData();

         $this->setVariable("driveQuota", $DriveAbout->getStorageQuota())
              ->setVariable("driveUploadSize", $DriveAbout->getMaxUploadSize())
              ->setVariable("Shareds", $Shareds)
              ->setVariable("MostDownload", $MostDownload)
              ->setVariable("LastShared", $LastShared)
              ->setVariable("pageTitle", lang("Dashboard"));
        
        if (Input::post("action") == "empty") {
            $this->empty();
        }

        $this->view("dashboard", "app");
    }

    private function empty()
    {
        $this->response->result = 0;

        $Google = $this->getVariable("Google");

        try {
            $Google->driveEmptyTrash();
        } catch (Exception $e) {
            $this->response->message = getError($e, lang("Failed to empty trash"));
            $this->response->notice = true;
            $this->jsonecho();
        }

        $this->response->result = 1;
        $this->jsonecho();
    }
}
