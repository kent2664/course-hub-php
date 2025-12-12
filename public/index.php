<?php 
    require __DIR__.'/../src/Interface/AuthProviderInterface.php';
    require __DIR__.'/../src/Interface/CourseProviderInterface.php';
    require __DIR__.'/../src/Provider/InMemoryAuthProvider.php';
    require __DIR__.'/../src/Provider/InMemoryCourseProvider.php';
    require __DIR__.'/../src/Service/AuthService.php';
    require __DIR__.'/../src/Service/CourseService.php';
    require __DIR__.'/../src/Model/Course.php';
    use App\Auth\InMemoryAuthProvider;
    use App\Course\InMemoryCourseProvider;
    use App\Services\AuthService;
    use App\Services\CourseService;

    $authProvider = new InMemoryAuthProvider();
    $authService = new AuthService($authProvider); //connecting the implementor class which implements the interface to the class which consumes the interface.

    //define course service with provider
    $courseProvider = new InMemoryCourseProvider();
    $courseService = new CourseService($courseProvider); //connecting the implementor class which implements the interface to the class which consumes the interface.


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
                    //implement logout feature with $authService
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
                    echo $authService->attemptLogin('alice',"password123");
                    echo $authService->status()."</br>";
                break;
            }
            break;

    }

?>