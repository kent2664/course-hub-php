<?php 

  namespace App\Services;
  use App\Interface\CourseProviderInterface;
  use App\Model\Course;
  class CourseService{
    private CourseProviderInterface $provider;

    
    function __construct(CourseProviderInterface $provider){
        $this->provider = $provider;
    }

    function getCourseList(): array{
        $returnCourse = $this->provider->getCourses();

        return $returnCourse;

    }

    function searchCourseList(string $target, string $searchTxt): array{
      $returnCourseList = [];
      switch($target){
        case "title":
          $returnCourseList = $this->provider->searchCourseTitle($searchTxt);
          break;
        case "teacher":
          $returnCourseList = $this->provider->searchCourseTeacher($searchTxt);
          break;
      }

      return $returnCourseList;
    }

    function insertCourse(Course $courseData): bool{
      return $this->provider->insertCourse($courseData);
    }

    function updateCourse(Course $courseData): bool{
      return $this->provider->updateCourse($courseData);
    }
    function deleteCourse(string $courseId): bool{
      return $this->provider->deleteCourse($courseId);
    }
    
  }


?>