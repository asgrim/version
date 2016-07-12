<?php

/**
 * This file is part of the Version package.
 *
 * Copyright (c) Nikola Posa <posa.nikola@gmail.com>
 *
 * For full copyright and license information, please refer to the LICENSE file,
 * located at the package root folder.
 */

namespace Version\Constraint;

use Version\Version;
use ReflectionClass;
use Version\Exception\InvalidConstraintException;
use Version\Exception\InvalidConstraintStringException;

/**
 * @author Nikola Posa <posa.nikola@gmail.com>
 */
class Constraint implements ConstraintInterface
{
    const OPERATOR_EQ = '=';
    const OPERATOR_NEQ = '!=';
    const OPERATOR_GT = '>';
    const OPERATOR_GTE = '>=';
    const OPERATOR_LT = '<';
    const OPERATOR_LTE = '<=';

    /**
     * @var string
     */
    protected $operator;

    /**
     * @var Version
     */
    protected $operand;

    /**
     * @var array
     */
    private static $validOperators;

    private function __construct()
    {
    }

    /**
     * @param string $operator
     * @param Version $operand
     * @return self
     */
    public static function create($operator, Version $operand)
    {
        if (!self::isOperatorValid($operator)) {
            throw InvalidConstraintException::forOperator($operator);
        }

        $constraint = new self();

        $constraint->operator = $operator;
        $constraint->operand = $operand;

        return $constraint;
    }

    protected static function isOperatorValid($operator)
    {
        return in_array($operator, self::getValidOperators());
    }

    protected static function getValidOperators()
    {
        if (isset(self::$validOperators)) {
            return self::$validOperators;
        }

        return self::$validOperators = array_filter(
            (new ReflectionClass(__CLASS__))->getConstants(),
            function ($value, $name) {
                return strpos($name, 'OPERATOR_') === 0;
            },
            ARRAY_FILTER_USE_BOTH
        );
    }

    /**
     * @param string $constraintString
     * @return self
     */
    public static function fromString($constraintString)
    {
        if (!is_string($constraintString)) {
            throw InvalidConstraintStringException::forInvalidType($constraintString);
        }

        $constraintString = trim($constraintString);

        if ($constraintString == '') {
            throw InvalidConstraintStringException::forEmptyCostraintString();
        }

        throw InvalidConstraintStringException::forConstraintString($constraintString);
    }

    /**
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @return Version
     */
    public function getOperand()
    {
        return $this->operand;
    }

    /**
     * {@inheritDoc}
     */
    public function assert(Version $version)
    {
        switch ($this->operator) {
            case self::OPERATOR_EQ :
                return $version->isEqualTo($this->operand);
            case self::OPERATOR_NEQ :
                return !$version->isEqualTo($this->operand);
            case self::OPERATOR_GT :
                return $version->isGreaterThan($this->operand);
            case self::OPERATOR_GTE :
                return $version->isGreaterOrEqualTo($this->operand);
            case self::OPERATOR_LT :
                return $version->isLessThan($this->operand);
            case self::OPERATOR_LTE :
                return $version->isLessOrEqualTo($this->operand);
            default :
                throw InvalidConstraintException::forOperator($this->operator);
        }
    }
}
