<?php 
    require __DIR__.'/../src/Interface/AuthProviderInterface.php';
    require __DIR__.'/../src/Interface/CourseProviderInterface.php';
    require __DIR__.'/../src/Provider/InMemoryAuthProvider.php';
    require __DIR__.'/../src/Provider/InMemoryCourseProvider.php';
    require __DIR__.'/../src/Provider/dataCourseProvider.php';
    require __DIR__.'/../src/Service/AuthService.php';
    require __DIR__.'/../src/Service/CourseService.php';
    require __DIR__.'/../src/Model/Course.php';

    use App\Auth\InMemoryAuthProvider;
    //use App\Course\InMemoryCourseProvider;
    use App\Course\dataCourseProvider;
    use App\Services\AuthService;
    use App\Services\CourseService;
    use App\Model\Course;

    $authProvider = new InMemoryAuthProvider();
    $authService = new AuthService($authProvider); //connecting the implementor class which implements the interface to the class which consumes the interface.

    //define course service with provider
    $courseProvider = new dataCourseProvider();
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
                    echo json_encode($courseService->getCourseList());
                break;
                case "searchcourse":
                    //login check needed
                     $authService->status();//check login status
                    //implement the feature that takes course info with $courseService
                    if(isset($_REQUEST["target"]) || isset($_REQUEST["searchtxt"])){
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
                    if(isset($_REQUEST["username"]) || isset($_REQUEST["password"])){
                        //sanitize input
                        $username = htmlspecialchars($_REQUEST["username"], ENT_QUOTES, 'UTF-8');
                        $password = htmlspecialchars($_REQUEST["password"], ENT_QUOTES, 'UTF-8');
                        echo $authService->attemptLogin($username,$password);
                    }else{
                        echo "Invalid login request.";
                    }
                break;
                case "insertcourse":
                    //login check needed
                    $authService->status();//check login status
                    if(isset($_REQUEST["id"]) || isset($_REQUEST["author"]) || isset($_REQUEST["title"]) || isset($_REQUEST["category"]) || isset($_REQUEST["rating"]) || isset($_REQUEST["hours"]) || isset($_REQUEST["level"]) || isset($_REQUEST["image"]) ){
                        //sanitize input
                        $target = htmlspecialchars($_REQUEST["id"], ENT_QUOTES, 'UTF-8');
                        $searchtxt = htmlspecialchars($_REQUEST["author"], ENT_QUOTES, 'UTF-8');

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
                        echo $courseService->insertCourse($courseData)." records inserted.";
                    }else{
                        echo "Invalid search request.";
                    }
                break;
                case "updatecourse":
                    $authService->status();//check login status
                    if(isset($_REQUEST["id"]) || isset($_REQUEST["author"]) || isset($_REQUEST["title"]) || isset($_REQUEST["category"]) || isset($_REQUEST["rating"]) || isset($_REQUEST["hours"]) || isset($_REQUEST["level"]) || isset($_REQUEST["image"]) ){
                        //sanitize input

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
                        echo $courseService->updateCourse($courseData)." records updated.";
                    }else{
                        echo "Invalid update request.";
                    }
                break;
                case "deletecourse":
                    $authService->status();//check login status
                    if(isset($_REQUEST["id"]) ){
                        //sanitize input
                        $target = htmlspecialchars($_REQUEST["id"], ENT_QUOTES, 'UTF-8');
                        echo $courseService->deleteCourse($target)." records deleted.";
                    }else{
                        echo "Invalid delete request.";
                    }
                break;
            }
            break;

    }

?>