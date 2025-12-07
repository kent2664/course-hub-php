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
            $filteredCourses = array_filter($this->$courses, function ($course) {
                return $course->title === $title;
            });
            return $filteredCourses;
        }

        //for Teacher
        public function searchCourseTeacher(string $authorName):array
        {
            //this should access DB with SQL
            $filteredCourses = array_filter($this->$courses, function ($course) {
                return $course->author === $authorName;
            });
            return $filteredCourses;
        }


    }


?>