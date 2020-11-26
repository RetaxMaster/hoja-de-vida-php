<?php

namespace App\Controllers;

use App\Models\Job;
use Respect\Validation\Validator as v;

class JobsController extends BaseController {

    // El request viene desde nuestro FrontController public/index.php
    public function getAddJobAction($request) {

        $responseMessage = null;
        
        if ($request->getMethod() == "POST") {
            
            $postData = $request->getParsedBody();

            $jobValidator = v::key("title", v::stringType()->notEmpty())
                            ->key("description", v::stringType()->notEmpty());

            try {

                $jobValidator->assert($postData);

                $files = $request->getUploadedFiles();
                $logo = $files["logo"];
                $fileName = "";
                
                if($logo->getError() == UPLOAD_ERR_OK) {

                    $fileName = $logo->getClientFilename();
                    $logo->moveTo("uploads/$fileName");

                }

                $job = new Job();
                $job->title = $postData["title"];
                $job->description = $postData["description"];
                $job->image = $fileName;
                $job->save();

                $responseMessage = "Saved";

            } catch (\Exception $e) {
                $responseMessage = $e->getMessage();
            }
     

        }

        return $this->renderHTML("addJob.twig", compact("responseMessage"));

    }

}

?>