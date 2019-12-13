<?php
/**
 * Settings Controller
 */
class SettingsController extends Controller
{
    /**
     * Index
     */
    public function index()
    {
        $AuthUser = $this->getVariable("AuthUser");

        if (!$AuthUser || !$AuthUser->isAdmin()){
            header("Location: " . APPURL);
            exit;
        }

        $this->setVariable("General", Controller::model("Setting", "general"))
             ->setVariable("pageTitle", lang("Settings"));

        if (Input::post("action") == "save") {
            $this->save();
        }

        $this->view("settings", "app");
    }

    private function save()
    {
        $this->response->result = 0;

        $General = $this->getVariable("General");

        // Check required fields
        $required_fields = ["name", "title", "description", "keywords"];

        foreach ($required_fields as $field) {
            if (!Input::post($field)) {
                $this->response->message = lang("Missing some of required data.");
                $this->jsonecho();
            }
        }

        $General->set("data.site_name", Input::post("name"))
                ->set("data.site_title", Input::post("title"))
                ->set("data.site_description", Input::post("description"))
                ->set("data.site_keywords", Input::post("keywords"))
                ->set("data.geonamesorg_username", Input::post("geonamesorg"))
                ->save();
        
        $this->response->result = 1;
        $this->response->message = lang("Changes saved!");
        $this->jsonecho();
    }
}
