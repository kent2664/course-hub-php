<?php 

    namespace App\Course;
    
    use App\Interface\CourseProviderInterface;
    use App\Model\Course;
    use App\Services\AuditService;

    class dataCourseProvider implements CourseProviderInterface{

        private AuditService $auditService;

        private array $courses = [];
        public function __construct(AuditService $auditService){
            $this->auditService = $auditService;
        }


        public function getCourses(): array
        {
            $errFlag = false;
            try{
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
            }catch(\Exception $e){
                throw new \Exception($e->getMessage(), $e->getCode());
            }
            
        }

        public function searchCourseTitle(string $title):array
        {
            try{
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
            }catch(\Exception $e){
                throw new \Exception($e->getMessage(), $e->getCode());
            }
        }

        //for Teacher
        public function searchCourseTeacher(string $authorName):array
        {
            try{
                $db = new \mysqli(DB_SERVER,DB_USER,DB_PASS,DB_NAME);
                if($db->connect_error)
                    throw new \Exception("Connection issue.",500);
                $searchCourse = $db->prepare("SELECT * FROM courses WHERE author = ? LIMIT 100");
                $searchCourse->bind_param("s",$authorName);
                $searchCourse->execute();
                $userData = $searchCourse->get_result();

                $searchCourse->close();
                $db->close();

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
            }catch(\Exception $e){
                throw new \Exception($e->getMessage(), $e->getCode());
            }
        }

        //insert course data to database
        public function insertCourse(Course $course): bool
        {
            try{
                //buid database connection
                $db = new \mysqli(DB_SERVER,DB_USER,DB_PASS,DB_NAME);
                $userid = require_auth($db);
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
                $this->auditService->outputLog($userid, true, "Successfully inserted course ");
                return true;
            }catch(\Exception $e){
                $this->auditService->outputLog($userid, false, "Failed to insert course");
                throw new \Exception($e->getMessage(), $e->getCode());
            }

        }
        
        //update course data in database

        public function updateCourse(Course $course): bool
        {
            try{
                //buid database connection
                $db = new \mysqli(DB_SERVER,DB_USER,DB_PASS,DB_NAME);
                $userid = require_auth($db);
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

                $this->auditService->outputLog($userid, true, "Successfully updated course with ID: " . $id);
                return $updateData;
            }catch(\Exception $e){
                    $this->auditService->outputLog($userid, false, "Failed to update course with ID: " . $id);
                    throw new \Exception($e->getMessage(), $e->getCode());
            }
        }

        // delete course data from database
        public function deleteCourse(string $courseId): bool
        {
            try{

                //buid database connection
                $db = new \mysqli(DB_SERVER,DB_USER,DB_PASS,DB_NAME);
                $userid = require_auth($db);
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

                $this->auditService->outputLog($userid, true, "Successfully deleted course with ID: " . $courseId);

                return $deleteData;
            }catch(\Exception $e){
                $this->auditService->outputLog($userid, false, "Failed to delete course with ID: " . $courseId);
                throw new \Exception($e->getMessage(), $e->getCode());
            }
        }

        public function getcoursedetailByCategory(string $category): array{
            try{
                $db = new \mysqli(DB_SERVER,DB_USER,DB_PASS,DB_NAME);
                if($db->connect_error)
                    throw new \Exception("Connection issue.",500);
                $loadCourse = $db->prepare("SELECT * FROM courses INNER JOIN coursesdetail ON courses.id = coursesdetail.id WHERE category = ? LIMIT 100");
                $loadCourse->bind_param("s", $category);
                $loadCourse->execute();
                $userData = $loadCourse->get_result();
                if($userData->num_rows === 0) throw new \Exception("No courses found in this category.",404);
                $loadCourse->close();
                $db->close();
                
                return $userData->fetch_assoc();
            }catch(\Exception $e){
                throw new \Exception($e->getMessage(), $e->getCode());
            }
        }


    }


?>