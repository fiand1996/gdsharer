<?php
/**
 * Users Controller
 */
class UsersController extends Controller
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

        $pageSize = 5;

        $Users = Controller::model("Users");
        $Users->search(Input::get("q"))
                ->setPageSize($pageSize)
                ->setPage(Input::get("page"))
                ->orderBy("id","DESC")
                ->fetchData();

        $Pagination = new DawPhpPagination\Pagination([
            "pp" => $pageSize,
            "css_class_p" => "pagination no-margin pull-right",
        ]);

        $Pagination->paginate($Users->getTotalCount());

        $this->setVariable("Users", $Users)
             ->setVariable("Pagination", $Pagination)
             ->setVariable("pageTitle", lang("Users"));

        if (Input::post("action") == "remove") {
            $this->remove();
        }

        $this->view("users", "app");
    }

    private function remove()
    {
        $this->response->result = 0;

        $AuthUser = $this->getVariable("AuthUser");

        $required_fields = ["id"];

        foreach ($required_fields as $field) {
            if (!Input::post($field)) {
                $this->response->message = lang("Missing some of required data.");
                $this->jsonecho();
            }
        }

        $User = Controller::model("User", Input::post("id"));

        if (!$User->isAvailable()) {
            $this->response->message = lang("User doesn't exist!");
            $this->jsonecho();
        }

        if ($AuthUser->get("id") == $User->get("id")) {
            $this->response->message = lang("You can not delete your own account!");
            $this->jsonecho();
        }

        $User->delete();

        $this->response->result = 1;
        $this->jsonecho();
    }
}
