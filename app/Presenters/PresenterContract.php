<?php

namespace App\Presenters;
use Illuminate\Http\Response;

/**
 * Interface PresenterContract
 * @package App
 */
interface PresenterContract
{
    /**
     * @param array|string $input
     * @param $code
     * @return Response
     */
    public function format($input, $code);
}