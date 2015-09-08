<?php

namespace Rentalhost\VanillaPermission;

/**
 * Class PermissionRule
 * @package Rentalhost\VanillaPermission
 */
class PermissionRule
{
    /**
     * Permission name.
     * @var string
     */
    public $name;

    /**
     * Permission title.
     * @var string
     */
    public $title;

    /**
     * Permission description.
     * @var string
     */
    public $description;

    /**
     * Permission level (zero-index).
     * @property-read
     * @var integer
     */
    public $level;

    /**
     * Construct a new rule.
     *
     * @param string $name        Rule name.
     * @param string $title       Rule title.
     * @param string $description Rule description.
     */
    public function __construct($name, $title = null, $description = null)
    {
        $this->name = $name;
        $this->title = $title;
        $this->description = $description;
        $this->level = substr_count($name, '.');
    }
}
