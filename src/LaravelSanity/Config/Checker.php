<?php

namespace LaravelSanity\Config;

use Illuminate\Support\Str;
use InvalidArgumentException;

class Checker
{
    /**
     * The configuration to check.
     *
     * @var array
     */
    private $config;

    /**
     * The target to check the configuration against.
     *
     * @var string
     */
    private $target;

    /**
     * The errors that happened during the checks.
     *
     * @var string[]
     */
    private $errors;

    /**
     * Checks the configuration against the given target.
     *
     * @param array  $config
     * @param string $target
     *
     * @return bool
     */
    public function check($config, $target)
    {
        $this->config = $config;
        $this->target = $target;
        $this->errors = [];

        $this->verifyTargetValidity();

        return $this->checkConfig();
    }

    /**
     * Verifies that the target can actually be checked.
     *
     * @return bool
     */
    private function verifyTargetValidity()
    {
        if (isset($this->config['checks'][$this->target])) {
            return true;
        }

        $validTargets = implode('", "', array_keys($this->config['checks']));

        throw new InvalidArgumentException(
            "Unknown target environment. Please use one of the following: \"$validTargets\"."
        );
    }

    /**
     * Actually checks the configuration.
     *
     * @return bool
     */
    private function checkConfig()
    {
        $valid = true;
        foreach ($this->config['checks'][$this->target] as $key => $value) {
            $valid = $this->checkSingleKeyValuePair($key, $value) && $valid;
        }

        return (bool) $valid;
    }

    /**
     * Checks a single key/value pair in the configuration.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return bool
     */
    private function checkSingleKeyValuePair($key, $value)
    {
        $actual = array_get($this->config, $key);
        $error = $this->checkValue($value, $actual);

        if (! $error) {
            return true;
        }

        $this->errors[] = "[$key] $error";

        return false;
    }

    /**
     * Checks a value with the different possible rules.
     *
     * @param mixed $value
     * @param mixed $actual
     *
     * @return string|null
     */
    private function checkValue($value, $actual)
    {
        if (! is_string($value)) {
            return $this->formatError([], $value === $actual, $value, $actual);
        }

        $rules = $this->parseRules($value);

        if (in_array('regex', $rules)) {
            $result = preg_match($value, $actual);
        } else {
            $result = $value === $actual;
        }

        if (in_array('not', $rules)) {
            $result = ! $result;
        }

        return $this->formatError($rules, $result, $value, $actual);
    }

    /**
     * Parses the possible rules of the string.
     *
     * @param string $value
     *
     * @return array
     */
    private function parseRules(&$value)
    {
        $rules = [];
        if (Str::startsWith($value, '__')) {
            $rules[] = $rule = Str::substr($value, 2, mb_strpos(Str::substr($value, 2), '_'));
            $value = Str::substr($value, Str::length($rule) + 4);

            $rules = array_merge($this->parseRules($value), $rules);
        }

        return $rules;
    }

    /**
     * Format the error message against the result.
     *
     * @param array  $rules
     * @param bool   $result
     * @param string $value
     * @param string $actual
     *
     * @return bool
     */
    private function formatError($rules, $result, $value, $actual)
    {
        if ($result) {
            return null;
        }

        $value = $this->formatBoolean($value);
        $actual = $this->formatBoolean($actual);

        if (in_array('regex', $rules)) {
            $message = "expected to match \"$value\", \"$actual\" found.";
        } else {
            $message = "expected to be \"$value\", \"$actual\" found.";
        }

        if (in_array('not', $rules)) {
            return Str::replaceFirst("expected to", "expected to NOT", $message);
        }

        return $message;
    }

    /**
     * @return string[]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param $actual
     *
     * @return string
     */
    private function formatBoolean($actual)
    {
        if (is_bool($actual)) {
            $actual = $actual ? 'true' : 'false';

            return $actual;
        }

        return $actual;
    }
}
