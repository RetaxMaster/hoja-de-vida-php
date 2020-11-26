<?php

namespace App\Controllers;

use App\Models\Job;

class JobsController extends BaseController {

    // El request viene desde nuestro FrontController public/index.php
    public function getAddJobAction($request) {
        
        if ($request->getMethod() == "POST") {

            $postData = $request->getParsedBody();
            $job = new Job();
            $job->title = $postData["title"];
            $job->description = $postData["description"];
            $job->save();

        }

        echo $this->renderHTML("addJob.twig");

    }

}

?>