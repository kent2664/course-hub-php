<?php
namespace App\Interface;

interface MyWorkProviderInterface
{
    //filtered by author
    public function getWorkByAuthor(string $author): array;

    //Retrieves MyWork items by author
    //If $author is empty, the implementation should return all items
    public function getWorkByStudent(string $student): array;

    //A method that returns all MyWork data.
    public function getAllWork(): array;

    //Updates the grade for a specific courseId
    //After updating, returns the updated records (may be multiple)
    public function updateGrade(string $courseId, string $grade): array;
}