<?php

namespace App\Domain\User;

enum UserRole: string{
    case COMPANY_ADMIN = "company_admin";
    case RECRUITER = "company_recuiter";
}