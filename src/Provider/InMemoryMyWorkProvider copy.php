<?php //Service ↔ Provider(role of mini db)

namespace App\MyWork;

use App\Interface\MyWorkProviderInterface;

class InMemoryMyWorkProvider implements MyWorkProviderInterface
{
    //$works acts like fake DB records
        //Constructor, runs when the object is created.
    private array $works = [];

    public function __construct()
    {
        // setting sample data
        $this->works = [
            [
                'id'       => 1,
                'author'   => 'Alice',
                'student'  => 'John',
                'courseId' => 'c1',
                'title'    => 'JS Assignment 1',
                'grade'    => 'A',
            ],
            [
                'id'       => 2,
                'author'   => 'Alice',
                'student'  => 'Emma',
                'courseId' => 'c1',
                'title'    => 'JS Assignment 1',
                'grade'    => 'B+',
            ],
            [
                'id'       => 3,
                'author'   => 'Kenta Onzoshi',
                'student'  => 'John',
                'courseId' => 'c2',
                'title'    => 'Python Assignment 1',
                'grade'    => 'B',
            ],
        ];
    }

    //filtering work by author
        //If author is empty, return all records
        //array_filter: records where author matches
        //array_values: resets array keys
    public function getWorkByAuthor(string $author): array
    {
        if ($author === '') {
            return $this->works;
        }

        return array_values(array_filter($this->works, function ($work) use ($author) {
            return $work['author'] === $author;
        }));
    }

    // //filtering work by student
    public function getWorkByStudent(string $student): array
    {
        return array_values(array_filter($this->works, function ($work) use ($student) {
            return $work['student'] === $student;
        }));
    }

    // Returns the whole dataset.
    public function getAllWork(): array
    {
        return $this->works;
    }

    // Updates grade for all records with matching courseId.
        // create array to save upgrade grade
        // Loops through each record by reference to modify it
        // Matches courseId → updates the grade
        // Adds updated record into the $updated array
        // Removes the reference to avoid unexpected behavior.
        // Returns only the updated records.
    public function updateGrade(string $courseId, string $grade): array
    {
        $updated = [];

        foreach ($this->works as &$work) { //if don't use & than its paste of $work ,so results is same
            if ($work['courseId'] === $courseId) {
                $work['grade'] = $grade;
                $updated[] = $work;
            }
        }
        unset($work); 

        return $updated;
    }
}

?>
