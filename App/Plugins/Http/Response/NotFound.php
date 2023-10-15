<?php


namespace App\Plugins\Http\Response;


use App\Plugins\Http\JsonStatus;

class NotFound extends JsonStatus
{
    /** @var int */
    const STATUS_CODE = 404;
    /** @var string */
    const STATUS_MESSAGE = 'Not found';

    /**
     * Constructor of this class
     * @param mixed $body
     */
    public function __construct($body = '')
    {
        if (isset($body['message'])) {
            $customMessage = $body['message'];
        } else {
            $customMessage = self::STATUS_MESSAGE;
        }

        $responseBody = ['message' => $customMessage];

        parent::__construct(self::STATUS_CODE, $customMessage, $responseBody);

        // OLD FUNCTION
        // public function __construct($body = '') {
        //     parent::__construct(self::STATUS_CODE, self::STATUS_MESSAGE, $body);
        // }
    }
}
