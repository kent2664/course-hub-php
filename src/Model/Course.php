<?php
// File: src/Model/Course.php

namespace App\Model;

class Course
{
    //properties
    private string $id;
    private ?string $author;
    private ?string $category;
    private ?string $title;
    private ?float $rating;
    private ?string $hours;
    private ?string $level;
    private ?string $image;

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
        ?string $id,
        ?string $author,
        ?string $category,
        ?string $title,
        ?float $rating,
        ?string $hours,
        ?string $level,
        ?string $image
    ) {
        $this->id = $id;
        $this->author = $author;
        $this->category = $category;
        $this->title = $title;
        $this->rating = $rating;
        $this->hours = $hours;
        $this->level = $level;
        $this->image = $image;
    }

    //getters
    public function getId(): ?string {
        return $this->id;
    }

    public function getAuthor(): ?string {
        return $this->author;
    }

    public function getCategory(): ?string {
        return $this->category;
    }

    public function getTitle(): ?string {
        return $this->title;
    }

    public function getRating(): ?float {
        return $this->rating;
    }

    public function getHours(): ?string {
        return $this->hours;
    }

    public function getLevel(): ?string {
        return $this->level;
    }

    public function getImage(): ?string {
        return $this->image;
    }


}