<?php 
    namespace App\Interface;
    interface CourseProviderInterface{

        public function getCourses(): array;

        public function searchCourseTitle(string $title): array;

        public function searchCourseTeacher(string $authorName): array;
    }


?>