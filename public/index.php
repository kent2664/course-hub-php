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
    require __DIR__ . '/../src/Provider/dataAuthProvider.php';

    require __DIR__ . '/../src/Service/webconfig.php';
    require __DIR__ . '/../src/Service/auth_token.php';
// require __DIR__ . '/../src/Service/helpers.php';

    require __DIR__.'/../src/Interface/MyWorkProviderInterface.php'; //interface
    require __DIR__.'/../src/Provider/DbMyWorkProvider.php'; //data processing
    require __DIR__.'/../src/Service/MyWorkService.php';//business logic
    
    use App\Auth\InMemoryAuthProvider;
    //use App\Course\InMemoryCourseProvider;
    use App\Course\dataCourseProvider;
    use App\Services\AuthService;
    use App\Services\CourseService;
    use App\Model\Course;

    use App\Services\AuditService;
    use Src\Common\Response;
    use App\Providers\DataAuthProvider;

    use App\Services\MyWorkService;
    use App\MyWork\DbMyWorkProvider;
   session_start();


//define course service with provider
// $courseProvider = new dataCourseProvider();
// $courseService = new CourseService($courseProvider); //connecting the implementor class which implements the interface to the class which consumes the interface.

    $authProvider = new DataAuthProvider();
    $authService = new AuthService($authProvider); //connecting the implementor class which implements the interface to the class which consumes the interface.

//define course service with provider
// $courseProvider = new InMemoryCourseProvider();
// $courseService = new CourseService($courseProvider); //connecting the implementor class which implements the interface to the class which consumes the interface.
    // error_reporting(E_ALL);
    // ini_set("display_errors", 1);
   
    //define course service with provider
    $myworkProvider = new DbMyWorkProvider($pdo);
    $myworkService = new MyWorkService($myworkProvider, $authService);
    $courseProvider = new dataCourseProvider();
    // $courseProvider = new InMemoryCourseProvider();
    $courseService = new CourseService($courseProvider); //connecting the implementor class which implements the interface to the class which consumes the interface.

    // $authProvider = new InMemoryAuthProvider();


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

                case "mywork":

                    // 2. query (JSON)
                    header('Content-Type: application/json');
                    $currentUser = $_SESSION["email"] ?? 'unknown';

                    checkKeys("author");
                    if(isset($_GET["author"])){
                         // 2-1 Filter by Author: Handles requests like /mywork?author=Alice
                        $author = htmlspecialchars($_GET["author"], ENT_QUOTES, 'UTF-8'); // Sanitize input to prevent XSS/injection attacks.
                        $data = $myworkService->getWorkByAuthor($author); 
                        Response::json($data, 200,"Success to course serching.");
                        echo json_encode([
                            "success" => true,
                            "data" => $data,
                            "message" => "My work data filtered by {$author}"
                        ]);                    
                    } else {
                        $data = $myworkService->getAllWork();
                        echo json_encode([
                            "success" => true,
                            "data" => $data,
                            "message" => "Your own MyWork data ({$currentUser})"
                        ]);
                    }
                break;

                    break;
                case "authme":
                    $authProvider->isAuthenticated();
                    break;
                case "courses":
                    //implement the feature that takes course info with $courseService
                    //echo "called ";
                    Response::json($courseService->getCourseList(), 200, "Course list fetched successfully.");
                break;
                case "searchcourse":
                    //login check needed
                    $authService->status();//check login status

                    checkKeys("target", "searchtxt");
                    //implement the feature that takes course info with $courseService
  
                    //sanitize input
                    $target = $_REQUEST["target"];;
                    $searchtxt = $_REQUEST["searchtxt"];
                    Response::json($courseService->searchCourseList($target, $searchtxt), 200, "Search results for '$searchtxt' in '$target'.");
                break;
                default: {
                    Response::json([], 404, "Endpoint not found.");

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

                case "mywork-grading":
                    checkKeys("courseId","grade");
                    header('Content-Type: application/json');
                    //1. Check login permissions
                    // $statusInfo = $authService->statusDetail();
                    // if (
                    //     !$statusInfo['logged_in'] ||
                    //     !in_array($statusInfo['role'], ['admin','teacher'], true)
                    // ) {
                    //     http_response_code(401); // fail 401[web:8]
                    //     echo json_encode([
                    //         "success" => false,
                    //         "message" => "Only admin or teacher can update grades."
                    //     ]);
                    //     break;
                    // }

                    // 2. Filtering (courseId, grade)
                    $courseId = isset($_POST['courseId'])
                        ? htmlspecialchars($_POST['courseId'], ENT_QUOTES, 'UTF-8')
                        : '';
                    $grade    = isset($_POST['grade'])
                        ? htmlspecialchars($_POST['grade'], ENT_QUOTES, 'UTF-8')
                        : '';

                    if ($courseId === '' || $grade === '') {
                        echo json_encode([
                            "success" => false,
                            "message" => "Invalid grading request. courseId and grade are required."
                        ]);
                        break;
                    }

                    // 3. DB update
                    $updatedWork = $myworkService->updateGrade($courseId, $grade);
                    
                    Response::json($updatedWork, 200,"Success to serching.");

                    break;
                case "insertcourse":
                    //login check needed
                    $authService->status();//check login status
                    //sanitize input
                    checkKeys("id", "author", "title", "category", "rating", "hours", "level", "image");
                    $courseData = new Course(
                            $_REQUEST["id"],
                            $_REQUEST["author"],
                            $_REQUEST["title"],
                            $_REQUEST["category"],
                            $_REQUEST["rating"],
                            $_REQUEST["hours"],
                            $_REQUEST["level"],
                            $_REQUEST["image"]
                        );
                    Response::json($courseService->insertCourse($courseData), 200, "Course inserted successfully.");
                break;
                case "updatecourse":
                    $authService->status();//check login status
                    //sanitize input
                    checkKeys("id", "author", "title", "category", "rating", "hours", "level", "image");

                    $courseData = new Course(
                            $_REQUEST["id"],
                            $_REQUEST["author"] == "" ? NULL: $_REQUEST["author"],
                            $_REQUEST["title"] == "" ? NULL: $_REQUEST["title"],
                            $_REQUEST["category"]== "" ? NULL: $_REQUEST["category"],
                            $_REQUEST["rating"]== "" ? NULL: $_REQUEST["rating"],
                            $_REQUEST["hours"]== "" ? NULL: $_REQUEST["hours"],
                            $_REQUEST["level"]== "" ? NULL: $_REQUEST["level"],
                            $_REQUEST["image"]== "" ? NULL: $_REQUEST["image"]
                        );
                    Response::json($courseService->updateCourse($courseData), 200, "Course updated successfully.");
                break;
                case "deletecourse":
                    $authService->status();//check login status
                    checkKeys("id");
                    Response::json($courseService->deleteCourse($_REQUEST["id"]), 200, "Course deleted successfully.");
                break;
            }
            break;

    }
} catch (Exception $err) {
    json_response($err->getMessage(), $err->getCode());
}

?>