<?php

namespace App\Models;

use  Illuminate\Database\Eloquent\Model;

class Job extends Model {

    protected $table = "jobs";
    protected $visible = 0;
    protected $months = 0;

    public function getDurationAsString() {
        $years = floor($this->months / 12);
        $extraMonths = $this->months % 12;
      
        return "Job duration: $years years $extraMonths months";
    }
    
}

?>