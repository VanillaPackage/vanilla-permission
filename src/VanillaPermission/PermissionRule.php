<?php

namespace Rentalhost\VanillaPermission;

/**
 * Class PermissionRule
 * @package Rentalhost\VanillaPermission
 */
class PermissionRule
{
    /**
     * Rule name.
     * @var string
     */
    public $name;

    /**
     * Rule title.
     * @var string
     */
    public $title;

    /**
     * Rule description.
     * @var string
     */
    public $description;

    /**
     * Rule internal data.
     * @var mixed
     */
    public $data;

    /**
     * Construct a new rule.
     *
     * @param string $name        Rule name.
     * @param string $title       Rule title.
     * @param string $description Rule description.
     * @param mixed  $data        Rule internal data.
     */
    public function __construct($name, $title = null, $description = null, $data = null)
    {
        $this->name        = $name;
        $this->title       = $title;
        $this->description = $description;
        $this->data        = $data;
    }

    /**
     * Returns the rule level based on root distance (zero-based).
     * @return int
     */
    public function getLevel()
    {
        return substr_count($this->name, '.');
    }

    /**
     * Get the internal rule data.
     * Optionally, you can pass key to return.
     *
     * @param string|null $key          Key name to return.
     * @param mixed|null  $defaultValue Value to return if key not exists.
     *
     * @return mixed
     */
    public function getData($key = null, $defaultValue = null)
    {
        // Returns the key, instead of all data.
        if ($key !== null) {
            if (!is_array($this->data) || !array_key_exists($key, $this->data)) {
                return $defaultValue;
            }

            return $this->data[$key];
        }

        return $this->data;
    }
}
