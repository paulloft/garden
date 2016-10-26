<?php

namespace Garden\Exception;

/**
 * Represents a 403 forbidden error.
 */
class Forbidden extends Client {
    /**
     * Initialize a {@link Forbidden}.
     *
     * @param string $message The error message or a one word resource name.
     * @param string $description A longer description for the error.
     */
    public function __construct($message = 'Forbidden', $description = null) {

        parent::__construct($message, 403, ['description' => $description]);
    }
}
