<?php

namespace App\Enums;

enum ErrorEnum: int
{
    case E000_UNKNOWN_ERROR = 0;
    case E001_VALIDATION_ERROR = 1;
    case E002_AUTHORIZATION_ERROR = 2;
    case E003_PROJECTS_NOT_FOUND = 3;
    case E004_AUTHORIZATION_LOGIC_BROKEN = 4;
    case E005_FILE_LOCKED = 5;
    case E006_NER_OR_FNF_TO_LOCK = 6;
    case E007_NER_SEE_DEP_PROJECTS = 7;
    case E008_PROJECT_EMPTY_OR_NO_LINE = 8;
    case E009_NER_OPER_PROJ_OR_NOT_EXIST = 9;
    case E010_REQUEST_PARSING_ERROR = 10;
    case E011_KEY_NOT_VALID = 11;
    case E012_PASS_NOT_VALID_OR_USER_BLOCKED = 12;

    public function label(): string
    {
        return match ($this) {
            static::E000_UNKNOWN_ERROR => __(static::E000_UNKNOWN_ERROR->name),
            static::E001_VALIDATION_ERROR => __(static::E001_VALIDATION_ERROR->name),
            static::E002_AUTHORIZATION_ERROR => __(static::E002_AUTHORIZATION_ERROR->name),
            static::E003_PROJECTS_NOT_FOUND => __(static::E003_PROJECTS_NOT_FOUND->name),
            static::E004_AUTHORIZATION_LOGIC_BROKEN => __(static::E004_AUTHORIZATION_LOGIC_BROKEN->name),
            static::E005_FILE_LOCKED => __(static::E005_FILE_LOCKED->name),
            static::E006_NER_OR_FNF_TO_LOCK => __(static::E006_NER_OR_FNF_TO_LOCK->name),
            static::E007_NER_SEE_DEP_PROJECTS => __(static::E007_NER_SEE_DEP_PROJECTS->name),
            static::E008_PROJECT_EMPTY_OR_NO_LINE => __(static::E008_PROJECT_EMPTY_OR_NO_LINE->name),
            static::E009_NER_OPER_PROJ_OR_NOT_EXIST => __(static::E009_NER_OPER_PROJ_OR_NOT_EXIST->name),
            static::E010_REQUEST_PARSING_ERROR => __(static::E010_REQUEST_PARSING_ERROR->name),
            static::E011_KEY_NOT_VALID => __(static::E011_KEY_NOT_VALID->name),
            static::E012_PASS_NOT_VALID_OR_USER_BLOCKED => __(static::E012_PASS_NOT_VALID_OR_USER_BLOCKED->name),
        };
    }
}
