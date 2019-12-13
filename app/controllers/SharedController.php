<?php
/**
 * Shared Controller
 *
 * @date 07 June 2019
 * @version 1.0
 * @copyright Fiand T <fiand96@yahoo.com>
 * @author Fiand T (https://github.com/fiand1996)
 */
class SharedController extends Controller
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

        $pageSize = 5;

        $Shareds = Controller::model("Shareds");
        $Shareds->search(Input::get("q"))
                ->setPageSize($pageSize)
                ->setPage(Input::get("page"))
                ->where("user_id", "=", $AuthUser->get("id"))
                ->orderBy("id","DESC")
                ->fetchData();

        $Pagination = new DawPhpPagination\Pagination([
            "pp" => $pageSize,
            "css_class_p" => "pagination no-margin pull-right",
        ]);

        $Pagination->paginate($Shareds->getTotalCount());

        $this->setVariable("Shareds", $Shareds)
             ->setVariable("Pagination", $Pagination)
             ->setVariable("pageTitle", lang("Shared Files"));

        $this->view("shared", "app");
    }
}
