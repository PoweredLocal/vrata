<?php

namespace App\Presenters;
use Illuminate\Http\Response;

/**
 * Class RawPresenter
 * @package App\Presenters
 */
class RawPresenter implements PresenterContract
{
    /**
     * @param array|string $input
     * @param $code
     * @return Response
     */
    public function format($input, $code)
    {
        if (is_array($input)) $input = json_encode($input);

        return new Response($input, $code, [
            'Content-Type' => 'application/json'
        ]);
    }
}