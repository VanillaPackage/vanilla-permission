<?php

namespace Rentalhost\VanillaPermission;

/**
 * Class Permission
 * @package Rentalhost\VanillaPermission
 */
class Permission
{
    /**
     * Store all rules.
     * @var PermissionRule[]
     */
    private $rules;

    /**
     * Contruct instance.
     */
    public function __construct()
    {
        $this->rules = [ ];
    }

    /**
     * Add a new rule to this permission instance.
     *
     * @param string|PermissionRule $nameOrRule  Rule or name to add.
     * @param string                $title       Rule title.
     * @param string                $description Rule description.
     */
    public function add($nameOrRule, $title = null, $description = null)
    {
        if ($nameOrRule instanceof PermissionRule) {
            $this->rules[] = $nameOrRule;

            return;
        }

        $this->rules[] = new PermissionRule($nameOrRule, $title, $description);
    }

    /**
     * Get a rule by name.
     *
     * @param  string $ruleName Rule name.
     *
     * @return PermissionRule|null
     */
    public function get($ruleName)
    {
        foreach ($this->rules as $permissionRule) {
            if ($permissionRule->name === $ruleName) {
                return $permissionRule;
            }
        }

        return null;
    }

    /**
     * Returns all permissions rules.
     * @return PermissionRule[]
     */
    public function getAll()
    {
        $rules = $this->rules;

        usort($rules, 'static::sortRules');

        return $rules;
    }

    /**
     * Returns all rule names.
     * @return string[]
     */
    public function getAllNames()
    {
        $ruleNames = [ ];

        foreach ($this->getAll() as $rule) {
            $ruleNames[] = $rule->name;
        }

        return $ruleNames;
    }

    /**
     * Returns only available rules based on array of names.
     * It'll exclude all disallowed rules and generate a new permissions list.
     *
     * @param  array $ruleNames Array of names.
     *
     * @return Permission
     */
    public function getOnly(array $ruleNames)
    {
        // Store pre-allowed rules.
        // Basically it'll do a simple check if rule exists on array.
        // Eg. "users.create" will be valid, even if "users" was not defined.
        // It'll be resolved in next step.
        $allowedRules = $this->filterPreAllowedRules($this->sortLevel($ruleNames));

        // Store post-allowed rules and your names.
        // After allow some rule, it'll marked as post-allowed.
        // So it make easy allow sub-rules of parent.
        $postAllowedRules = [ ];
        $postAllowedRulesNames = [ ];

        // Next step will unset all rules that not have defined parents.
        foreach ($allowedRules as $allowedRule) {
            // All zero-level rule is truly allowed.
            // Eg. "users", "billings", ...
            if (strpos($allowedRule->name, '.') === false) {
                $postAllowedRules[] = $allowedRule;
                $postAllowedRulesNames[] = $allowedRule->name;
                continue;
            }

            // Else, check if the parent of this rule was post-allowed.
            $allowedRuleParent = substr($allowedRule->name, 0, strrpos($allowedRule->name, '.'));
            if (in_array($allowedRuleParent, $postAllowedRulesNames, true)) {
                $postAllowedRules[] = $allowedRule;
                $postAllowedRulesNames[] = $allowedRule->name;
                continue;
            }
        }

        // Returns a new permission rules.
        $permission = new self;
        $permission->rules = array_values($postAllowedRules);

        return $permission;
    }

    /**
     * Get allowed rules without check parents.
     *
     * @param  string[] $ruleNames Rule names.
     *
     * @return PermissionRule[]
     */
    private function filterPreAllowedRules(array $ruleNames)
    {
        $allowedRules = [ ];

        foreach ($this->rules as $rule) {
            if (in_array($rule->name, $ruleNames, true)) {
                $allowedRules[] = $rule;
            }
        }

        return $allowedRules;
    }

    /**
     * Sort a rules array by level.
     *
     * @param  string[] $ruleNames Rule names.
     *
     * @return string[]
     */
    private function sortLevel(array $ruleNames)
    {
        $levelCount = [ ];

        foreach ($ruleNames as $ruleName) {
            $levelCount[$ruleName] = substr_count($ruleName, '.');
        }

        asort($levelCount);

        return array_keys($levelCount);
    }

    /**
     * Sort rules by rule name.
     *
     * @param PermissionRule $left  Left permission.
     * @param PermissionRule $right Right permission.
     *
     * @return int
     */
    private static function sortRules($left, $right)
    {
        return strcmp($left->name, $right->name);
    }

    /**
     * Check if rule was defined.
     * Not necessarily that is fully allowed.
     *
     * @param  string $ruleName Rule name.
     *
     * @return boolean
     */
    public function has($ruleName)
    {
        return $this->get($ruleName) !== null;
    }

    /**
     * Check if all rules was defined.
     *
     * @param  array $ruleNames Rules names.
     *
     * @return boolean
     */
    public function hasAll(array $ruleNames)
    {
        foreach ($ruleNames as $ruleName) {
            if (!$this->has($ruleName)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if one of rules was defined.
     *
     * @param  array $ruleNames Rules names.
     *
     * @return boolean
     */
    public function hasOne(array $ruleNames)
    {
        foreach ($ruleNames as $ruleName) {
            if ($this->has($ruleName)) {
                return true;
            }
        }

        return false;
    }
}
