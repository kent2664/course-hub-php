<?php 
    namespace App\Interface;
    use App\Model\Course;
    interface CourseProviderInterface{

        public function getCourses(): array;

        public function searchCourseTitle(string $title): array;

        public function searchCourseTeacher(string $authorName): array;

        public function insertCourse(Course $course): bool;

        public function updateCourse(Course $course): bool;

        public function deleteCourse(string $courseId): bool;


    }


?>