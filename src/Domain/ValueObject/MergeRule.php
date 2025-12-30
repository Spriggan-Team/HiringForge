<?php

namespace App\Domain\ValueObject;

enum MergeRule{
    case FULL_OVERWRITE;
    case PARTIAL_MERGE;
}