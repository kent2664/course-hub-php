<?php 

    namespace App\Course;
    
    use App\Interface\CourseProviderInterface;
    use App\Model\Course;

    require __DIR__.'/../Service/webconfig.php';

    class dataCourseProvider implements CourseProviderInterface{

        public function __construct(){

        }


        public function getCourses(): array
        {

            $errFlag = false;
            $db = new \mysqli(DB_SERVER,DB_USER,DB_PASS,DB_NAME);
            if($db->connect_error)
                throw new \Exception("Connection issue.",500);
            $loadCourse = $db->prepare("SELECT * FROM courses LIMIT 100");
            $loadCourse->execute();
            $userData = $loadCourse->get_result();
            if($userData->num_rows === 0) $errFlag = true;

            $loadCourse->close();
            $db->close();
            if($errFlag) throw new \Exception("Username or Password invalid",401);

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
                throw new \Exception("Connection issue.",500);
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
                throw new \Exception("Connection issue.",500);
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

        //insert course data to database
        public function insertCourse(Course $course): bool
        {
            //buid database connection
            $db = new \mysqli(DB_SERVER,DB_USER,DB_PASS,DB_NAME);
            if($db->connect_error)
                throw new \Exception("Connection issue.",500);
            //prepare insert statement
            $insertCourse = $db->prepare("INSERT INTO courses (id, author, category, title, rating, hours, level, image) VALUES (?,?,?,?,?,?,?,?)");
            $insertCourse->bind_param("ssssdsss",
                $id,
                $author,
                $category,
                $title,
                $rating,
                $hours,
                $level,
                $image
            );
            $id = $course->getId();
            $author = $course->getAuthor();
            $category = $course->getCategory();
            $title = $course->getTitle();
            $rating = $course->getRating();
            $hours = $course->getHours();
            $level = $course->getLevel();
            $image = $course->getImage();
 
            //execute insert
            $result = $insertCourse->execute();
            $insertData = $insertCourse->get_result();
            //check if any row is affected
            if($insertCourse->affected_rows === 0)  throw new \Exception("Data insert faild.",500);
            $insertCourse->close();
            $db->close();
            return true;
        }
        
        //update course data in database

        public function updateCourse(Course $course): bool
        {
            //buid database connection
            $db = new \mysqli(DB_SERVER,DB_USER,DB_PASS,DB_NAME);
            if($db->connect_error)
                throw new \Exception("Connection issue.",500);
            //prepare update statement if the value is not null use it otherwise keep the old value
            $updateCourse = $db->prepare("UPDATE courses SET author=COALESCE(?,author), category=COALESCE(?,category), title=COALESCE(?,title), rating=COALESCE(?,rating), hours=COALESCE(?,hours), level=COALESCE(?,level), image=COALESCE(?,image) WHERE id=?");
            $updateCourse->bind_param("sssdssss",
                $author,
                $category,
                $title,
                $rating,
                $hours,
                $level,
                $image,
                $id
            );
            $author = $course->getAuthor();
            $category = $course->getCategory();
            $title = $course->getTitle();
            $rating = $course->getRating();
            $hours = $course->getHours();
            $level = $course->getLevel();
            $image = $course->getImage();
            $id = $course->getId();
            //execute update
            $result = $updateCourse->execute();
            //get the result and check if any row is affected
            $updateData = $updateCourse->get_result();
            if($updateCourse->affected_rows === 0 )  throw new \Exception("Data update faild.",500);
            //close connections
            $updateCourse->close();
            $db->close();

            return $updateData;
        }

        // delete course data from database
        public function deleteCourse(string $courseId): bool
        {
            //buid database connection
            $db = new \mysqli(DB_SERVER,DB_USER,DB_PASS,DB_NAME);
            if($db->connect_error)
                throw new Exception("Connection issue.",500);
            //prepare update statement if the value is not null use it otherwise keep the old value
            $deleteCourse = $db->prepare("DELETE FROM courses WHERE id=?");
            $deleteCourse->bind_param("s",$courseId);
            //execute delete
            $result = $deleteCourse->execute();
            $deleteData = $deleteCourse->get_result();
            //check if any row is affected
            if($deleteCourse->affected_rows === 0)  throw new \Exception("Data delete faild.",500);
            //close connections
            $deleteCourse->close();
            $db->close();

            return $deleteData;
        }


    }


?>