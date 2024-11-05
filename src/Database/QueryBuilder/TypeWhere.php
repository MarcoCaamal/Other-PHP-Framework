<?php

namespace SMFramework\Database\QueryBuilder;

enum TypeWhere: int
{
    case NORMAL = 0;
    case WRAPPED = 1;
    case ALL = 3;
    case ANY = 4;
    case NONE = 5;
}
