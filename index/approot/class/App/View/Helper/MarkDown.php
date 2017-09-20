<?php
namespace App\View\Helper;

class MarkDown
{
    public function __invoke($text) 
    {
        include_once INC_DIR . "Michelf/MarkdownExtra.inc.php";
        $markdown = new \Michelf\MarkdownExtra();
        return $markdown::defaultTransform($text);
    }
}