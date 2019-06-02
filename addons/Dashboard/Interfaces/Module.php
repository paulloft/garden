<?php

namespace Addons\Dashboard\Interfaces;

use Garden\Exception\NotFound;

interface Module {
    /**
     * instance function
     * @param array ...$args
     * @return mixed
     */
    public static function instance(...$args);

    /**
     * Rendering function
     * @param array $params
     * @return string
     * @throws NotFound
     */
    public function render(array $params = []): string;
}