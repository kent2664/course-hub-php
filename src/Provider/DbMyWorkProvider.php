<?php

namespace App\MyWork;

use App\Interface\MyWorkProviderInterface;
use PDO; //Imports PHP’s built-in PDO class for database access (PHP Data Objects)

class DbMyWorkProvider implements MyWorkProviderInterface //Defines a database-based provider class that implements the interface
{
    private PDO $pdo; //Private property to store the PDO database connection

    public function __construct(PDO $pdo) //Constructor that receives a PDO object (dependency injection)/ __(ㅡMagic Method: PHP calls automatically)
    {
        $this->pdo = $pdo; //Stores the passed PDO object in the class property
    }

    // Filter by author
    public function getWorkByAuthor(string $author): array
    {//if the author string is empty
        //If no author is given, fetch all records
        //author is provided
        //Prepares a SQL query with an author condition
        //Executes the query with the author value bound
        //Returns all rows as an associative array

        if ($author === '') {
            $stmt = $this->pdo->query("SELECT * FROM mywork");
        } else {
            $stmt = $this->pdo->prepare("SELECT * FROM mywork WHERE author = ?");
            $stmt->execute([$author]);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC); //::=Call the add method directly from the class
    }

    // Filter by student
        //Prepares SQL query with student condition
        //Executes the query with the student value
        //Returns the fetched results

    public function getWorkByStudent(string $student): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM mywork WHERE student = ?");
        $stmt->execute([$student]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // fetching all data
        //
    public function getAllWork(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM mywork");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update grade based on courseId
    public function updateGrade(string $courseId, string $grade): array
    {
        $update = $this->pdo->prepare(
            "UPDATE mywork 
            SET grade = :grade, status = 'graded'
            WHERE courseId = :courseId"
        );
        $update->execute([
            ':grade'    => $grade,
            ':courseId' => $courseId,
        ]);

        // Re-fetch updated rows to return them
        $select = $this->pdo->prepare(
            "SELECT * FROM mywork WHERE courseId = ?"
        );
        $select->execute([$courseId]);

        return $select->fetchAll(PDO::FETCH_ASSOC);
    }
}
