<?php
namespace app\common\helper;

/**
 * Model对象工具类
 *
 * @author Haishen Tian
 */
abstract class ModelHelper
{
    // 条件操作符
    const CONDITION_NOT = 'NOT';
    const CONDITION_AND = 'AND';
    const CONDITION_OR = 'OR';
    const CONDITION_BETWEEN = 'BETWEEN';
    const CONDITION_NOT_BETWEEN = 'NOT BETWEEN';
    const CONDITION_IN = 'IN';
    const CONDITION_NOT_IN = 'NOT IN';
    const CONDITION_LIKE = 'LIKE';
    const CONDITION_NOT_LIKE = 'NOT LIKE';
    const CONDITION_OR_LIKE = 'OR LIKE';
    const CONDITION_OR_NOT_LIKE = 'OR NOT LIKE';
    const CONDITION_EXISTS = 'EXISTS';
    const CONDITION_NOT_EXISTS = 'NOT EXISTS';

    /**
     * 创建一个表达式对象
     *
     * @param string $expression
     * @param array $params
     * @return \yii\db\Expression
     */
    public static function expression($expression, $params = [])
    {
        return new \yii\db\Expression($expression, $params);
    }

    /**
     * 创建一个PDO值对象
     *
     * @param $value
     * @param $type
     * @return \yii\db\PdoValue
     */
    public static function newPdoValue($value, $type)
    {
        return new \yii\db\PdoValue($value, $type);
    }

    public static function condition($column, $op, $value)
    {
        return [$op, $column, $value];
    }

    public static function betweenColumn($value, $intervalStartColumn, $intervalEndColumn)
    {
        return [self::CONDITION_BETWEEN, $value, $intervalStartColumn, $intervalEndColumn];
    }

    public static function notBetweenColumn($value, $intervalStartColumn, $intervalEndColumn)
    {
        return [self::CONDITION_NOT_BETWEEN, $value, $intervalStartColumn, $intervalEndColumn];
    }

    public static function not($condition)
    {
        return [self::CONDITION_NOT, $condition];
    }

    public static function and($condition)
    {
        if (is_array($condition)) {
            array_unshift($condition, self::CONDITION_AND);
            return $condition;
        }
        return [self::CONDITION_AND, $condition];
    }

    public static function or($condition)
    {
        if (is_array($condition)) {
            array_unshift($condition, self::CONDITION_OR);
            return $condition;
        }
        return [self::CONDITION_OR, $condition];
    }

    public static function between($column, $intervalStart, $intervalEnd)
    {
        return [self::CONDITION_BETWEEN, $column, $intervalStart, $intervalEnd];
    }

    public static function notBetween($column, $intervalStart, $intervalEnd)
    {
        return [self::CONDITION_NOT_BETWEEN, $column, $intervalStart, $intervalEnd];
    }

    public static function in($column, $values)
    {
        return [self::CONDITION_IN, $column, $values];
    }

    public static function notIn($column, $values)
    {
        return [self::CONDITION_NOT_IN, $column, $values];
    }

    public static function like($column, $value)
    {
        return [self::CONDITION_LIKE, $column, $value];
    }

    public static function notLike($column, $value)
    {
        return [self::CONDITION_NOT_LIKE, $column, $value];
    }

    public static function orLike($column, $value)
    {
        return [self::CONDITION_OR_LIKE, $column, $value];
    }

    public static function orNotLike($column, $value)
    {
        return [self::CONDITION_OR_NOT_LIKE, $column, $value];
    }

    public static function exists($column, $query)
    {
        return [self::CONDITION_EXISTS, $column, $query];
    }

    public static function notExists($column, $query)
    {
        return [self::CONDITION_NOT_EXISTS, $column, $query];
    }
}