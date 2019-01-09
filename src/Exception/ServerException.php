<?php

namespace TPerformant\API\Exception;

class ServerException extends APIException {
    public static function create(\Http\Client\Common\Exception\ServerErrorException $e) {
        $message = sprintf(
            'API server error (%s %s): %s on %s',
            $e->getResponse()->getStatusCode(),
            $e->getResponse()->getReasonPhrase(),
            $e->getMessage(),
            $e->getRequest()->getUri()
        );

        return new self($message, $e->getResponse()->getStatusCode(), $e);
    }
}
