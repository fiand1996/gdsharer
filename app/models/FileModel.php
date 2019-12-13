<?php
/**
 * File Model
 *
 * @date 07 June 2019
 * @version 1.0
 * @copyright Fiand T <fiand96@yahoo.com>
 * @author Fiand T (https://github.com/fiand1996)
 */
class FileModel extends DataEntry
{
    /**
     * Extend parents constructor and select entry
     * @param mixed $uniqid Value of the unique identifier
     */
    public function __construct($uniqid = 0)
    {
        parent::__construct();
        $this->select($uniqid);
    }

    /**
     * Select entry with uniqid
     * @param  int|string $uniqid Value of the any unique field
     * @return self
     */
    public function select($uniqid)
    {
        if (is_int($uniqid) || ctype_digit($uniqid)) {
            $col = $uniqid > 0 ? "id" : null;
        } else {
            $col = "slug";
        }

        if ($col) {
            $query = DB::table(TABLE_SHAREDS)
                ->where($col, "=", $uniqid)
                ->limit(1)
                ->select("*");
            if ($query->count() == 1) {
                $resp = $query->get();
                $r = $resp[0];

                foreach ($r as $field => $value) {
                    $this->set($field, $value);
                }

                $this->is_available = true;
            } else {
                $this->data = array();
                $this->is_available = false;
            }
        }

        return $this;
    }

      /**
     * Extend default values
     * @return self
     */
    public function extendDefaults()
    {
     $defaults = array(
          "user_id" => 0,
          "file_id" => "",
          "file_name" => "",
          "file_size" => "",
          "file_mimeType" => "",
          "file_viewLink" => "",
          "file_contentLink" => "",
          "slug" => "",
          "modified" => date("Y-m-d H:i:s")
     );


     foreach ($defaults as $field => $value) {
          if (is_null($this->get($field)))
               $this->set($field, $value);
     }
    }

    /**
     * Update selected entry with Data
     */
    public function update()
    {
     if (!$this->isAvailable())
          return false;

     $this->extendDefaults();

     $id = DB::table(TABLE_SHAREDS)
          ->where("id", "=", $this->get("id"))
          ->update(array(
               "user_id" => $this->get("user_id"),
               "file_id" => $this->get("file_id"),
               "file_name" => $this->get("file_name"),
               "file_size" => $this->get("file_size"),
               "file_mimeType" => $this->get("file_mimeType"),
               "file_viewLink" => $this->get("file_viewLink"),
               "file_contentLink" => $this->get("file_contentLink"),
               "download_count" => $this->get("download_count"),
               "slug" => $this->get("slug")
          ));

     return $this;
    }
}
