<?php
/**
 * GDShare Controller
 *
 * @date 07 June 2019
 * @version 1.0
 * @copyright Fiand T <fiand96@yahoo.com>
 * @author Fiand T (https://github.com/fiand1996)
 */
class GDShareController extends Controller
{
    public static function updateDrive($Google, $AuthUser)
    {
        $Mydrive = $Google->getListFiles($AuthUser, 200);

        foreach ($Mydrive as $file) {
            $Mydrive = Controller::model("Mydrive", $file->id);

            if (!$Mydrive->isAvailable()) {
                $date = date("Y-m-d H:i:s", strtotime($file->createdTime));
                $thumnail = $file->hasThumbnail ? $file->thumbnailLink : null;
                $Mydrive->set("user_id", $AuthUser->get("id"))
                        ->set("file_id", $file->id)
                        ->set("file_name", $file->name)
                        ->set("file_size", isset($file->size) ? $file->size : 0)
                        ->set("file_mimeType", $file->mimeType)
                        ->set("file_thumbnail", $thumnail)
                        ->set("file_viewLink", $file->webViewLink)
                        ->set("file_contentLink", $file->webContentLink)
                        ->set("created", $date)
                        ->save();
            }
        }
    }
}
