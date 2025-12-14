<?php 

    namespace App\Course;
    
    use App\Interface\CourseProviderInterface;
    use App\Model\Course;

    require __DIR__.'/../webconfig.php';

    class dataCourseProvider implements CourseProviderInterface{
        private $courses = [];

        public function __construct(){

        }


        public function getCourses(): array
        {

            $errFlag = false;
            $db = new \mysqli(DB_SERVER,DB_USER,DB_PASS,DB_NAME);
            if($db->connect_error)
                throw new Exception("Connection issue.",500);
            $loadCourse = $db->prepare("SELECT * FROM courses LIMIT 100");
            $loadCourse->execute();
            $userData = $loadCourse->get_result();
            if($userData->num_rows === 0) $errFlag = true;

            $loadCourse->close();
            $db->close();
            if($errFlag) throw new Exception("Username or Password invalid",401);

            while($courseData = $userData->fetch_assoc()){
                $course = new Course(
                    $courseData['id'],
                    $courseData['author'],
                    $courseData['category'],
                    $courseData['title'],
                    $courseData['rating'],
                    $courseData['hours'],
                    $courseData['level'],
                    $courseData['image']
                );
                array_push($this->courses, $course);
            }
            return $this->courses;
        }

        public function searchCourseTitle(string $title):array
        {
            //$errFlag = false;
            $db = new \mysqli(DB_SERVER,DB_USER,DB_PASS,DB_NAME);
            if($db->connect_error)
                throw new Exception("Connection issue.",500);
            $searchCourse = $db->prepare("SELECT * FROM courses WHERE title LIKE ? LIMIT 100");
            $searchCourse->bind_param("s", $title);
            $title = "%".$title."%";
            $searchCourse->execute();
            $userData = $searchCourse->get_result();
            //if($userData->num_rows === 0) $errFlag = true;

            $searchCourse->close();
            $db->close();
            //if($errFlag) throw new Exception("Username or Password invalid",401);

            while($courseData = $userData->fetch_assoc()){
                $course = new Course(
                    $courseData['id'],
                    $courseData['author'],
                    $courseData['category'],
                    $courseData['title'],
                    $courseData['rating'],
                    $courseData['hours'],
                    $courseData['level'],
                    $courseData['image']
                );
                array_push($this->courses, $course);
            }
            return $this->courses;
        }

        //for Teacher
        public function searchCourseTeacher(string $authorName):array
        {
            //$errFlag = false;
            $db = new \mysqli(DB_SERVER,DB_USER,DB_PASS,DB_NAME);
            if($db->connect_error)
                throw new Exception("Connection issue.",500);
            $searchCourse = $db->prepare("SELECT * FROM courses WHERE author = ? LIMIT 100");
            $searchCourse->bind_param("s",$authorName);
            $searchCourse->execute();
            $userData = $searchCourse->get_result();
            //if($userData->num_rows === 0) $errFlag = true;

            $searchCourse->close();
            $db->close();
            //if($errFlag) throw new Exception("Username or Password invalid",401);

            while($courseData = $userData->fetch_assoc()){
                $course = new Course(
                    $courseData['id'],
                    $courseData['author'],
                    $courseData['category'],
                    $courseData['title'],
                    $courseData['rating'],
                    $courseData['hours'],
                    $courseData['level'],
                    $courseData['image']
                );
                array_push($this->courses, $course);
            }
            return $this->courses;
        }


    }


?>