<?php

namespace SMFramework\Database\QueryBuilder;

use SMFramework\Database\QueryBuilder\Contracts\QueryBuilderContract;

trait UseBuilder
{
    /**
     *
     * @var class-string<QueryBuilderContract>
     */
    public static string $builderClassString;
    private QueryBuilder $builder;
    /**
     * Summary of setBuilderClassString
     * @param class-string<QueryBuilderContract> $builderClassString
     * @return void
     */
    public static function setBuilderClassString(string $builderClassString)
    {
        self::$builderClassString = $builderClassString;
    }
}
