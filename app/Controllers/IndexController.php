<?php

namespace App\Controllers;

use App\Models\{ Job, Project };

class IndexController {
    
    public function indexAction() {

        $jobs = Job::all();
        $project1 = new Project("Project 1", "Descripción");
        $projects = [$project1];

        $name = 'RetaxMaster';
        $limitMonths = 2000;

        include("../views/index.php");

    }

}


?>