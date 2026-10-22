<?php
/**
 * PesaPlan — Input validator.
 */

declare(strict_types=1);

namespace App\Core;

final class Validator
{
    private array $data;
    private array $rules;
    private array $errors = [];

    public function __construct(array $data, array $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
    }

    public function validate(): bool
    {
        foreach ($this->rules as $field => $ruleStr) {
            $rules = explode('|', $ruleStr);
            $value = $this->data[$field] ?? null;
            foreach ($rules as $rule) {
                $rule = trim($rule);
                if ($rule === 'nullable' && ($value === null || $value === '')) {
                    break;
                }
                if ($rule === 'required' && ($value === null || $value === '')) {
                    $this->errors[$field] = ucfirst($field) . ' inahitajika.';
                    break;
                }
                if ($rule === 'string' && !is_string($value)) {
                    $this->errors[$field] = ucfirst($field) . ' lazima iwe maandishi.';
                    break;
                }
                if ($rule === 'integer' && !preg_match('/^-?\d+$/', (string) $value)) {
                    $this->errors[$field] = ucfirst($field) . ' lazima iwe namba.';
                    break;
                }
                if ($rule === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field] = ucfirst($field) . ' si sahihi.';
                    break;
                }
                if ($rule === 'date' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $value)) {
                    $this->errors[$field] = ucfirst($field) . ' si tarehe sahihi.';
                    break;
                }
                if (str_starts_with($rule, 'min:')) {
                    $min = (float) substr($rule, 4);
                    if (is_string($value) && mb_strlen($value) < $min) {
                        $this->errors[$field] = ucfirst($field) . " inapaswa kuwa na herufi $min au zaidi.";
                        break;
                    }
                    if (is_numeric($value) && (float) $value < $min) {
                        $this->errors[$field] = ucfirst($field) . " inapaswa kuwa $min au zaidi.";
                        break;
                    }
                }
                if (str_starts_with($rule, 'max:')) {
                    $max = (float) substr($rule, 4);
                    if (is_string($value) && mb_strlen($value) > $max) {
                        $this->errors[$field] = ucfirst($field) . " inaweza kuwa na herufi $max tu.";
                        break;
                    }
                    if (is_numeric($value) && (float) $value > $max) {
                        $this->errors[$field] = ucfirst($field) . " inaweza kuwa $max tu.";
                        break;
                    }
                }
                if (str_starts_with($rule, 'in:')) {
                    $allowed = explode(',', substr($rule, 3));
                    if (!in_array((string) $value, $allowed, true)) {
                        $this->errors[$field] = ucfirst($field) . ' si chaguo halali.';
                        break;
                    }
                }
            }
        }
        return empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public static function amount(string $value): ?float
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }
        // Allow "1200", "1,200", "1200.50"
        $value = str_replace(',', '', $value);
        if (!is_numeric($value)) {
            return null;
        }
        return (float) $value;
    }

    public static function sanitize(string $value): string
    {
        return trim(strip_tags($value));
    }
}
