<?php 
/**
 * Users Model
 *
 * @date 07 June 2019
 * @version 1.0
 * @copyright Fiand T <fiand96@yahoo.com>
 * @author Fiand T (https://github.com/fiand1996)
 */
class UsersModel extends DataList
{    
     /**
      * Initialize
      */
     public function __construct()
     {
          $this->setQuery(DB::table(TABLE_USERS));
     }

     /**
     * Perform a search if $searh_query provided
     * @param  string $search_query 
     * @return self               
     */
    public function search($search_query)
    {
        parent::search($search_query);
        $search_query = $this->getSearchQuery();

        if (!$search_query) {
            return $this;
        }

        $query = $this->getQuery();
        $query->where(TABLE_USERS.".firstname", "LIKE", "%".$search_query."%");
        $query->orWhere(TABLE_USERS.".lastname", "LIKE", "%".$search_query."%");
        return $this;
    }
}
