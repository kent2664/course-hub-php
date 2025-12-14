<?php 
    session_start();
    
    require __DIR__.'/../src/Interface/AuthProviderInterface.php';
    require __DIR__.'/../src/Interface/CourseProviderInterface.php';
    require __DIR__.'/../src/Provider/InMemoryAuthProvider.php';
    require __DIR__.'/../src/Provider/InMemoryCourseProvider.php';
    require __DIR__.'/../src/Service/AuthService.php';
    require __DIR__.'/../src/Service/CourseService.php';
    require __DIR__.'/../src/Service/AuditService.php';
    require __DIR__.'/../src/Model/Course.php';
    require __DIR__.'/../src/validation.php';
    require __DIR__.'/../src/Service/Functions.php';
    require __DIR__.'/../src/Service/webconfig.php';
    use App\Auth\InMemoryAuthProvider;
    use App\Course\InMemoryCourseProvider;
    use App\Services\AuthService;
    use App\Services\CourseService;
    use App\Services\AuditService;

    $authProvider = new InMemoryAuthProvider();
    $authService = new AuthService($authProvider); //connecting the implementor class which implements the interface to the class which consumes the interface.

    //define course service with provider
    $courseProvider = new InMemoryCourseProvider();
    $courseService = new CourseService($courseProvider); //connecting the implementor class which implements the interface to the class which consumes the interface.
    $auditService = new AuditService();

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
                    //implement the feature that takes achievement info with $myworkService
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
                    //implement login feature with $authService
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
            }
            break;

        }
    }catch(Exception $err){
        http_response_code($err->getCode());
        echo "Error: ".$err->getMessage();
    }

?>