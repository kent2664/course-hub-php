<?php 

    namespace App\Course;
    use App\Interface\CourseProviderInterface;
    use App\Model\Course;

    class InMemoryCourseProvider implements CourseProviderInterface{
        private $courses = [];

        public function __construct(){
            $this->courses = [ new Course(
            'c1',
            'Milad Torabi',
            'Js',
            'Java Script for Front Developer',
            4.9,
            '64 hours',
            'Beginner'
        ),
        new Course( 
            'c2', 
            'Kenta Onzoshi',
            'Python',
            'Python for Engineer',
            4.9,
            '64 hours',
            'Beginner'
        ),];
        }


        public function getCourses(): array
        {

            return $this->courses;
        }

        public function searchCourseTitle(string $title):array
        {
            //this should access DB with SQL
            $filteredCourses = array_filter($this->courses, function ($course) use ($title){
                return $course->title === $title;
            });
            return $filteredCourses;
        }

        //for Teacher
        public function searchCourseTeacher(string $authorName):array
        {

            //this should access DB with SQL
            $filteredCourses = array_filter($this->courses, function ($course) use ($authorName){
                return $course->author === $authorName;
            });
            return $filteredCourses;
        }

        //Insert Course
        public function insertCourse(Course $course): bool
        {
            array_push($this->courses, $course);
            return true;
        }
        //Update Course
        public function updateCourse(Course $course): bool
        {
            foreach($this->courses as $index => $existingCourse){
                if($existingCourse->id === $course->id){
                    $this->courses[$index] = $course;
                    return true;
                }
            }
            return false;
        }

        //Delete Course
        public function deleteCourse(string $courseId): bool
        {
            foreach($this->courses as $index => $existingCourse){
                if($existingCourse->id === $courseId){
                    array_splice($this->courses, $index, 1);
                    return true;
                }
            }
            return false;
        }

    }


?>