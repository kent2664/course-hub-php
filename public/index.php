<?php 
    require __DIR__.'/../src/interface/AuthProviderInterface.php';
    require __DIR__.'/../src/Provider/InMemoryAuthProvider.php';
    require __DIR__.'/../src/Service/AuthService.php';
    use App\Auth\InMemoryAuthProvider;
    use App\Services\AuthService;

    $provider = new InMemoryAuthProvider();
    $authService = new AuthService($provider); //connecting the implementor class which implements the interface to the class which consumes the interface.

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
                break;
            }
            break;
        case "POST":
            // when the form submit, this case will be executed.
            switch(basename($_SERVER["PATH_INFO"])){
                case "login":
                    //implement login feature with $authService
                    // echo $authService->attemptLogin('alice',"password123");
                    // echo $authService->status()."</br>";
                break;
            }
            break;

    }

?>