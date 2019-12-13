<?php
/**
 * Index Controller
 *
 * @date 07 June 2019
 * @version 1.0
 * @copyright Fiand T <fiand96@yahoo.com>
 * @author Fiand T (https://github.com/fiand1996)
 */
class UploadsController extends Controller
{
    /**
     * Index
     */
    public function index()
    {
        $Route = $this->getVariable("Route");
        $filename = $Route->params->file;

        $filepath = ROOTPATH . "/uploads/users/{$filename}";

        if (!file_exists($filepath)) {
            header("HTTP/1.0 404 Not Found");
            $this->response->code = 404;
            $this->response->message = "File not found";
            $this->jsonecho();
        }

        $handle = @fopen($filepath, "rb");
        $size = @getimagesize($filepath);

        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($filepath)) . ' GMT');

        if (Input::server("HTTP_IF_MODIFIED_SINCE") and
            filemtime($filepath) == strtotime(Input::server("HTTP_IF_MODIFIED_SINCE"))) {
            header('HTTP/1.1 304 Not Modified');
        }

        header('Content-Type: ' . $size['mime']);
        header('Content-Length: ' . filesize($filepath));

        ob_end_clean();
        @fpassthru($handle);
        exit;
    }
}
