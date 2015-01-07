<?php
/**
 * BaseModel
 **/

namespace Easemob\Model;

class BaseModel {
    protected $easemob = null;

    public function __construct($easemob){
        $this->easemob = $easemob;
    }

}
?>
