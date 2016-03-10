<?php
/**
 * @author Todd Burry <todd@vanillaforums.com>
 * @copyright 2009-2014 Vanilla Forums Inc.
 * @license MIT
 */

namespace Garden\Exception;

use Garden\Response;

/**
 * An exception that represents an entire http response.
 *
 * The {@link Response} is sort of a psuedo-exception and may not result in an error at all.
 * Rather, you can throw a {@link Response} from within a dispatch and the thrown response will be serverd.
 */
class Response {
    /**
     * @var Response The response for this exception.
     */
    protected $response;

    /**
     * Initialize an instance of a {@link Response} class.
     *
     * @param Response $response The response the exception will serve.
     */
    public function __construct(Response $response) {
        $this->response = $response;
    }

    /**
     * Get the {@link Response} corresponding to this exception.
     *
     * @return Response
     */
    public function getResponse() {
        return $this->response;
    }
}
