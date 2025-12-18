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

        try{
        if ($author === '') {
            $stmt = $this->pdo->query("SELECT * FROM mywork");
        } else {
            $stmt = $this->pdo->prepare("SELECT * FROM mywork WHERE author = ?");
            $stmt->execute([$author]);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC); //::=Call the add method directly from the class
        }
        //handling error        
        catch (\Exception $e){
            throw new \Exception($e->getMessage(), $e->getCode());
        }

    }

    // Filter by student
        //Prepares SQL query with student condition
        //Executes the query with the student value
        //Returns the fetched results

    public function getWorkByStudent(string $student): array
    {
        try{
        $stmt = $this->pdo->prepare("SELECT * FROM mywork WHERE student = ?");
        $stmt->execute([$student]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        //handling error        
        catch (\Exception $e){
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    // fetching all data
        //
    public function getAllWork(): array
    {
        try{
        $stmt = $this->pdo->query("SELECT * FROM mywork");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        //handling error        
        catch (\Exception $e){
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    // Update grade based on courseId
    public function updateGrade(string $courseId, string $grade): array
    {   
        try{
            $update = $this->pdo->prepare(
                "UPDATE mywork 
                SET grade = :grade, status = 'graded'
                WHERE courseId = :courseId"
            );
            $update->execute([
                ':grade'    => $grade,
                ':courseId' => $courseId,
            ]);

            $db = new \mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
            $userid = require_auth($db);

            // Re-fetch updated rows to return them
            $select = $this->pdo->prepare(
                "SELECT * FROM mywork WHERE courseId = ?"
            );
            $select->execute([$courseId]);

            $this->auditService->outputLog($userid, true, "Successfully updated course grade for courseId: $courseId");

            return $select->fetchAll(PDO::FETCH_ASSOC);
        }
        //handling error        
        catch (\Exception $e){
            $this->auditService->outputLog($userid, false, "Failed to update course grade for courseId: $courseId");
            throw new \Exception($e->getMessage(), $e->getCode());

        }

    }
}
