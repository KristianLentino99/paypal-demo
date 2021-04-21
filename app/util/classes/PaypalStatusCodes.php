<?php


namespace App\util\classes;


interface PaypalStatusCodes
{

    //POSSIBLE STATUS CODES
    const STATUS_CODE_CREATED = 201;
    const STATUS_CODE_OK = 200;
    const STATUS_CODE_ACCEPTED = 202;
    const STATUS_CODE_BAD_REQUEST = 400;
    const STATUS_CODE_UNAUTHORIZE = 401;
    const STATUS_CODE_SERVER_ERROR = 500;

}
