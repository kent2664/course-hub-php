<?php 
    session_start(); // Start the session
    
    require __DIR__.'/../src/Interface/AuthProviderInterface.php';
    require __DIR__.'/../src/Interface/CourseProviderInterface.php';
    require __DIR__.'/../src/Provider/InMemoryAuthProvider.php';
    require __DIR__.'/../src/Provider/InMemoryCourseProvider.php';
    require __DIR__.'/../src/Service/AuthService.php';
    require __DIR__.'/../src/Service/CourseService.php';
    require __DIR__.'/../src/Service/AuditService.php';
    require __DIR__.'/../src/Model/Course.php';
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
        switch($_SERVER["REQUEST_METHOD"]){
        case "GET":
            //check the APi is booklist
            switch(basename($_SERVER["PATH_INFO"])){
                case "logout":
                    if(isset($_SESSION["username"])){
                        $auditService->logLogout($_SESSION["username"]);
                        session_destroy();
                        echo "Logged out successfully!";
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
                        $target = htmlspecialchars($_REQUEST["target"], ENT_QUOTES, 'UTF-8');
                        $searchtxt = htmlspecialchars($_REQUEST["searchtxt"], ENT_QUOTES, 'UTF-8');
                        print_r($courseService->searchCourseList($target,$searchtxt));
                    }else{
                        echo "Invalid search request.";
                    }
            }
            break;
        case "POST":
            // when the form submit, this case will be executed.
            switch(basename($_SERVER["PATH_INFO"])){
                case "login":
                    //implement login feature with $authService
                    if(isset($_POST["username"]) && isset($_POST["password"])){
                        // Sanitizin data received (ENT_QUOTES protect single quotation, transforming then into codes)
                        $username = htmlspecialchars($_POST["username"], ENT_QUOTES, 'UTF-8');
                        $password = $_POST["password"];
                        
                        // Login attempt
                        $loginSuccess = $authService->attemptLogin($username, $password);
                        // Audit the login
                        $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
                        $auditService->logLogin($username, strpos($loginSuccess, 'successful') !== false, $ip);
                        
                        if(strpos($loginSuccess, 'successful') !== false){
                            $_SESSION["username"] = $username;
                            $_SESSION["authenticated"] = true;
                            echo "Login successful!";
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

?>