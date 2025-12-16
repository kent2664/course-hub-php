<?php
    require __DIR__.'/../src/Interface/AuthProviderInterface.php';
    require __DIR__.'/../src/Interface/CourseProviderInterface.php';
    require __DIR__.'/../src/Provider/InMemoryAuthProvider.php';
    require __DIR__.'/../src/Provider/InMemoryCourseProvider.php';
    require __DIR__.'/../src/Provider/dataCourseProvider.php';
    require __DIR__.'/../src/Service/AuthService.php';
    require __DIR__.'/../src/Service/CourseService.php';
    require __DIR__.'/../src/Model/Course.php';
    require __DIR__ . '/../src/Service/AuditService.php';
    require __DIR__ . '/../src/validation.php';
    require __DIR__ . '/../src/Common/Response.php';
    require __DIR__ . '/../src/Service/Functions.php';
    require __DIR__ . '/../src/Service/webconfig.php';
    require __DIR__ . '/../src/Provider/dataAuthProvider.php';
    
    use App\Auth\InMemoryAuthProvider;
    //use App\Course\InMemoryCourseProvider;
    use App\Course\dataCourseProvider;
    use App\Services\AuthService;
    use App\Services\CourseService;
    use App\Model\Course;

use App\Providers\DataAuthProvider;
session_start();


use App\Course\InMemoryCourseProvider;
use App\Services\AuditService;
use Src\Common\Response;

//define course service with provider
$courseProvider = new dataCourseProvider();
$courseService = new CourseService($courseProvider); //connecting the implementor class which implements the interface to the class which consumes the interface.

$authProvider = new DataAuthProvider();
// $authProvider = new InMemoryAuthProvider();
$authService = new AuthService($authProvider); //connecting the implementor class which implements the interface to the class which consumes the interface.

$auditService = new AuditService();

// sample codes
// echo $authService->status() . "</br>";

// echo $authService->attemptLogin('alice',"password123");
// echo $authService->status()."</br>";

// $provider->logout();
// echo $authService->status(). "</br>";
try {
    $errFlag = false;
    switch ($_SERVER["REQUEST_METHOD"]) {
        case "GET":
            //check the APi is booklist
            switch (basename($_SERVER["PATH_INFO"])) {
                case "logout":
                    if (isset($_SESSION["username"])) {
                        $auditService->logLogout($_SESSION["username"]);
                        session_unset();
                        session_destroy();
                        echo "Logged out successfully!";
                    } else {
                        throw new Exception("Logout error", 400);
                    }
                    break;
                case "auth/me":
                    // Return current authenticated user info from session
                    if (isset($_SESSION["authenticated"]) && $_SESSION["authenticated"] === true && isset($_SESSION["email"])) {
                        $user = [
                            'email' => $_SESSION["email"],
                            'authenticated' => true
                        ];
                        Response::json($user, 200, ($_SESSION["email"] . " is logged in."));
                    } else {
                        Response::json([], 401, "No user logged in.");
                    }
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
                    if(isset($_REQUEST["target"]) || isset($_REQUEST["searchtxt"])){
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
                    checkKeys("email", "password", "role");
                    registerUser($_REQUEST["email"], $_REQUEST["password"], $_REQUEST["role"]);
                    break;
                case "login":
                    checkKeys("email", "password");
                    $email = $_REQUEST["email"];
                    $password = $_REQUEST["password"];
                    $authProvider->login($email,$password);
                    break;
            }
            break;

    }
} catch (Exception $err) {
    http_response_code($err->getCode());
    echo "Error: " . $err->getMessage();
}

?>