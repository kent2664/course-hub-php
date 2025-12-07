<?php
// File: src/Model/Course.php

namespace App\Model;

class Course
{
    //properties
    public string $id;
    public string $author;
    public string $category;
    public string $title;
    public float $rating;
    public string $hours;
    public string $level;

    /**
     * Course constractor
     *
     * @param string $id 
     * @param string $author 
     * @param string $category 
     * @param string $title 
     * @param float $rating
     * @param string $hours 
     * @param string $level 
     */
    public function __construct(
        string $id,
        string $author,
        string $category,
        string $title,
        float $rating,
        string $hours,
        string $level
    ) {
        $this->id = $id;
        $this->author = $author;
        $this->category = $category;
        $this->title = $title;
        $this->rating = $rating;
        $this->hours = $hours;
        $this->level = $level;
    }
}