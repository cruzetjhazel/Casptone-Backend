<?php

namespace App\Enums;

enum AccountType: string
{
    case Client = 'client';
    case Photographer = 'photographer';
    case Administrator = 'administrator';
}