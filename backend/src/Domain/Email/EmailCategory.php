<?php


namespace App\Domain\Email;

enum EmailCategory: string{
    case DEFAULT = "default";
    case WARNING = "warning";
}

?>