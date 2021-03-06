<?php
namespace App\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Redirect;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\ServerRequest;
use Respect\Validation\Validator as v;

class AuthController extends BaseController {

    public function getLogin() {
        return $this->renderHTML('login.twig');
    }

    public function postLogin(ServerRequest $request) {

        $postData = $request->getParsedBody();
        $user = User::where("email", $postData["email"])->first();

        if($user) {

            if (password_verify($postData["password"], $user->password)) {
                $_SESSION["userId"] = $user->id;
                return new RedirectResponse("/hoja-de-vida-php/admin");
            }
            else {
                $responseMessage = "Bad credentials";
            }
            
        }
        else {

            $responseMessage = "Bad credentials";

        }

        return $this->renderHTML("login.twig", compact("responseMessage"));

        
    }

    public function getLogout() {

        unset($_SESSION["userId"]);
        return new RedirectResponse("/hoja-de-vida-php/login");

    }

} 