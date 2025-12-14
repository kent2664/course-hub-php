<?php//save logic to handle data

namespace App\Services;

use App\Interface\MyWorkProviderInterface;
use App\Services\AuthService;

class MyWorkService
{
    private MyWorkProviderInterface $provider; //handles data access
    private AuthService $authService; //handles authentication & authorization

    //Constructor. Runs when the object is created and receives $provider and $authService
        ////Stores the passed-in $provider and $authService into the object’s properties.
    public function __construct(MyWorkProviderInterface $provider, AuthService $authService)
    {
        $this->provider    = $provider;
        $this->authService = $authService;
    }

    
    // Gets work list by author
        // If author is empty, return all work
        // takes a string $author and returns an array
    public function getWorkByAuthor(string $author): array 
    {
        // actual data retrieval to the provider.
        return $this->provider->getWorkByAuthor($author);
    }

    //Gets work list by student name
    public function getWorkByStudent(string $student): array
    {
        return $this->provider->getWorkByStudent($student);
    }

    // Method that returns all work items.
        //Calls the provider to get all work records.
    public function getAllWork(): array
    {
        return $this->provider->getAllWork();
    }

    //handles grade updates
        //actual grade update retrieval to the provider.
    public function updateGrade(string $courseId, string $grade): array
    {
        return $this->provider->updateGrade($courseId, $grade);
    }
}

?>
