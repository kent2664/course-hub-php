<?php
require __DIR__ . '/../src/Interface/AuthProviderInterface.php';
// require __DIR__ . '/../src/Interface/CourseProviderInterface.php';
// require __DIR__ . '/../src/Provider/InMemoryCourseProvider.php';
// require __DIR__ . '/../src/Provider/dataCourseProvider.php';
require __DIR__ . '/../src/Service/AuthService.php';
// require __DIR__ . '/../src/Service/CourseService.php';
// require __DIR__ . '/../src/Model/Course.php';
require __DIR__ . '/../src/Service/AuditService.php';
require __DIR__ . '/../src/validation.php';
require __DIR__ . '/../src/Common/Response.php';
require __DIR__ . '/../src/Service/webconfig.php';
require __DIR__ . '/../src/Provider/dataAuthProvider.php';
require __DIR__ . '/../src/Service/auth_token.php';
// require __DIR__ . '/../src/Service/helpers.php';

// use App\Course\dataCourseProvider;
use App\Services\AuthService;
// use App\Services\CourseService;
// use App\Model\Course;
// use App\Course\InMemoryCourseProvider;
use App\Services\AuditService;
use Src\Common\Response;
use App\Providers\DataAuthProvider;
session_start();


//define course service with provider
// $courseProvider = new dataCourseProvider();
// $courseService = new CourseService($courseProvider); //connecting the implementor class which implements the interface to the class which consumes the interface.

$authProvider = new DataAuthProvider();
$authService = new AuthService($authProvider); //connecting the implementor class which implements the interface to the class which consumes the interface.

//define course service with provider
// $courseProvider = new InMemoryCourseProvider();
// $courseService = new CourseService($courseProvider); //connecting the implementor class which implements the interface to the class which consumes the interface.
$auditService = new AuditService();


try {
    $errFlag = false;
    switch ($_SERVER["REQUEST_METHOD"]) {
        case "GET":
            //check the APi is booklist
            switch (basename($_SERVER["PATH_INFO"])) {
                case "logout":
                    // Use provider logout which will revoke the bearer token
                    $authProvider->logout();
                    break;
                case "authme":
                    $authProvider->isAuthenticated();
                    break;
                case "mywork":
                    //implement the feature that takes achievement info with $myworkService
                    break;
                case "courses":
                    //implement the feature that takes course info with $courseService
                    //echo "called ";
                    echo json_encode($courseService->getCourseList());
                    break;
                case "searchcourse":
                    //login check needed
                    $authService->status();//check login status
                    //implement the feature that takes course info with $courseService
                    if (isset($_REQUEST["target"]) || isset($_REQUEST["searchtxt"])) {
                        //sanitize input
                        $target = input_sanitizer($_REQUEST["target"], 'text');
                        $searchtxt = input_sanitizer($_REQUEST["searchtxt"], 'text');
                        print_r($courseService->searchCourseList($target, $searchtxt));
                    } else {
                        echo "Invalid search request.";
                    }
            }
            break;
        case "POST":
            // when the form submit, this case will be executed.
            switch (basename($_SERVER["PATH_INFO"])) {
                case "register":
                    if(!isset($_REQUEST["role"])){
                        $_REQUEST["role"] = "student";
                    } 
                    if($_REQUEST["role"]=="teacher"||$_REQUEST["role"]=="admin"){
                        if(!$authProvider->isAdmin()){
                            Response::json([],400,"Not authorized");
                        }
                    }
                    // Check and sanitize keys
                    checkKeys("email", "password", "role");
                    // Call the registerUser function
                    $authProvider->registerUser($_REQUEST["email"], $_REQUEST["password"], $_REQUEST["role"]);
                    break;
                case "login":
                    // Check and sanitize the keys
                    checkKeys("email", "password");
                    // Define variables
                    $email = $_REQUEST["email"];
                    $password = $_REQUEST["password"];
                    // Call the function
                    $authProvider->login($email, $password);
                    break;
            }
            break;

    }
} catch (Exception $err) {
    json_response($err->getMessage(), $err->getCode());
}

?>