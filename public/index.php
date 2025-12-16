<?php 
    // session_start();
    // ini_set('display_errors', 1);
    // ini_set('display_startup_errors', 1);
    // error_reporting(E_ALL);
    
    require __DIR__.'/../src/Interface/AuthProviderInterface.php';
    require __DIR__.'/../src/Interface/CourseProviderInterface.php';
    require __DIR__.'/../src/Provider/InMemoryAuthProvider.php';
    require __DIR__.'/../src/Provider/InMemoryCourseProvider.php';
    require __DIR__.'/../src/Service/AuthService.php';
    require __DIR__.'/../src/Service/CourseService.php';
    require __DIR__.'/../src/Service/AuditService.php';
    require __DIR__.'/../src/Model/Course.php';
    require __DIR__.'/../src/validation.php';
    require __DIR__.'/../src/Common/Response.php';
    require __DIR__.'/../src/Service/Functions.php';
    require __DIR__.'/../src/Service/webconfig.php';
    require __DIR__.'/../src/Interface/MyWorkProviderInterface.php'; //interface
    require __DIR__.'/../src/Provider/DbMyWorkProvider.php'; //data processing
    require __DIR__.'/../src/Service/MyWorkService.php';//business logic
    use App\Auth\InMemoryAuthProvider;
    use App\Course\InMemoryCourseProvider;
    use App\Services\AuthService;
    use App\Services\CourseService;
    use App\Services\AuditService;
    // use App\MyWork\InMemoryMyWorkProvider;
    use App\Services\MyWorkService;
    use App\MyWork\DbMyWorkProvider;

    $authProvider = new InMemoryAuthProvider();
    $authService = new AuthService($authProvider); //connecting the implementor class which implements the interface to the class which consumes the interface.

    //define course service with provider
    $courseProvider = new InMemoryCourseProvider();
    $courseService = new CourseService($courseProvider); //connecting the implementor class which implements the interface to the class which consumes the interface.
    $auditService = new AuditService();
    // $myworkProvider = new InMemoryMyWorkProvider(); // creates a provider object to handle MyWork data.
    // $myworkService = new MyWorkService($myworkProvider, $authService); //for MyWork business logic, injecting the $authService for permission checks.
    $myworkProvider = new DbMyWorkProvider($pdo);
    $myworkService = new MyWorkService($myworkProvider, $authService);
    

    // sample codes
    // echo $authService->status() . "</br>";

    // echo $authService->attemptLogin('alice',"password123");
    // echo $authService->status()."</br>";

    // $provider->logout();
    // echo $authService->status(). "</br>";
    try{
        $errFlag = false;
        switch($_SERVER["REQUEST_METHOD"]){
        case "GET":
            //check the APi is booklist
            switch(basename($_SERVER["PATH_INFO"])){
                case "logout":
                    if(isset($_SESSION["username"])){
                        $auditService->logLogout($_SESSION["username"]);
                        session_destroy();
                        echo "Logged out successfully!";
                    } else{
                        throw new Exception("Logout error",400);
                    }
                break;

                case "mywork":
                    // --- CONSOLIDATED MYWORK GET REQUESTS ---
                    // This endpoint handles both /mywork?author=X and /mywork?student=Y queries.
                    
                    // 1.login check
                    session_start(); 
                    // if ($authService->status() !== 'logged_in') {
                    //     header('Content-Type: application/json');
                    //     http_response_code(401); // Unauthorized
                    //     echo json_encode(["success" => false, "message" => "Login is required to access MyWork data."]);
                    //     break;
                    // } 

                    // 2. query (JSON)
                    header('Content-Type: application/json');
                    $currentUser = $_SESSION["email"] ?? 'unknown';
                    
                    if(isset($_GET["author"])){
                         // 2-1 Filter by Author: Handles requests like /mywork?author=Alice
                        $author = htmlspecialchars($_GET["author"], ENT_QUOTES, 'UTF-8'); // Sanitize input to prevent XSS/injection attacks.
                        $data = $myworkService->getWorkByAuthor($author);

                        echo json_encode([
                            "success" => true,
                            "data" => $data,
                            "message" => "My work data filtered by {$author}"
                        ]);
                    } else if(isset($_GET["student"])) {
                         // 2-2 Filter by Student(for admin & author): Handles requests like /mywork?student=John
                        $student = htmlspecialchars($_GET["student"], ENT_QUOTES, 'UTF-8'); // Sanitize input.
                        $data = $myworkService->getWorkByStudent($student);

                        echo json_encode([
                            "success" => true,
                            "data" => $data,
                            "message" => "Student's my work data filtered by {$student}"
                        ]);
                    } else {
                        $data = $myworkService->getWorkByAuthor($currentUser);
                        echo json_encode([
                            "success" => true,
                            "data" => $data,
                            "message" => "Your own MyWork data ({$currentUser})"
                        ]);
                    }
                break;

                case "courses":
                    //implement the feature that takes course info with $courseService
                    //echo "called ";
                    print_r($courseService->getCourseList());
                break;
                case "searchcourse":
                    //login check needed
                     $authService->status();//check login status
                    //implement the feature that takes course info with $courseService
                    if(isset($_REQUEST["target"]) && isset($_REQUEST["searchtxt"])){
                        //sanitize input
                        $target = input_sanitizer($_REQUEST["target"], 'text');
                        $searchtxt = input_sanitizer($_REQUEST["searchtxt"], 'text');
                        print_r($courseService->searchCourseList($target,$searchtxt));
                    }else{
                        echo "Invalid search request.";
                    }
            }
            break;
        case "POST":
            // when the form submit, this case will be executed.
            switch(basename($_SERVER["PATH_INFO"])){
                case "register":
                    checkKeys("email","password","role");
                    registerUser($_REQUEST["email"],$_REQUEST["password"],$_REQUEST["role"]);
                break;
                case "login":
                    checkKeys("email","password");
                    // Implement login feature with $authService
                    if(isset($_POST["email"]) && isset($_POST["password"])){
                        // Sanitized data received using input_sanitizer function
                        $email = input_sanitizer($_POST["email"], 'email');
                        $password = password_hash(input_sanitizer($_POST["password"], 'pass'),PASSWORD_BCRYPT,['cost'=>10]);
                        
                        // Login attempt
                        $loginSuccess = $authService->attemptLogin($email, $password);
                        // Audit the login
                        $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
                        $auditService->logLogin($email, strpos($loginSuccess, 'successful') !== false, $ip);
                        
                        if(strpos($loginSuccess, 'successful') !== false){
                            $_SESSION["email"] = $email;
                            $_SESSION["authenticated"] = true;
                            echo "Login successful! {$_SESSION["email"]}";
                        } else {
                            echo "Login failed!";
                        }
                    }else{
                        echo "Invalid login request.";
                    }
                break;

                case "mywork-grading":
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

                    // 4. results
                    echo json_encode([
                        "success" => true,
                        "data"    => $updatedWork,
                        "message" => "Course: {$courseId} updated the grade"
                    ]);
                break;
            }
            break;

        }
    }catch(Exception $err){
        http_response_code($err->getCode());
        // echo "Error: ".$err->getMessage();
    }

?>