<?php

namespace App\Controllers;

use App\Models\{ Job, Project };

class IndexController extends BaseController {
    
    public function indexAction() {

        $jobs = Job::all();
        $project1 = new Project("Project 1", "Descripción");
        $projects = [$project1];

        // Filtro los trabajos con mas de 15 meses
        $limitMonths = 15;
        /* $jobs = array_filter($jobs->toArray(), function($job) use ($limitMonths) {
            return $job["months"] >= $limitMonths;
        }); */

        $name = 'RetaxMaster';

        return $this->renderHTML("index.twig", compact("name", "jobs"));

    }

}


?>