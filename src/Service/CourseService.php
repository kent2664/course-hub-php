<?php 

  namespace App\Services;
  use App\Interface\CourseProviderInterface;
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

    
  }


?>