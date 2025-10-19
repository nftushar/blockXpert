<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Main BlockXpert plugin class. Initializes all features.
 */
class BlockXpert {
    /** @var BlockXpert_Blocks */
    public $blocks;
    /** @var BlockXpert_REST */
    public $rest;
    /** @var BlockXpert_Admin_Settings */
    public $admin_settings;

    public function __construct() {
        $this->blocks = new BlockXpert_Blocks();
        $this->rest = new BlockXpert_REST();
        $this->admin_settings = new BlockXpert_Admin_Settings();
    }
} 