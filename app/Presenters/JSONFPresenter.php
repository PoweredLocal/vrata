<?php

namespace App\Presenters;

use App\Exceptions\DataFormatException;
use Illuminate\Http\Response;

/**
 * Class JSONPresenter
 * @package App\Presenters
 */
class JSONPresenter implements PresenterContract
{
    /**
     * @param array|string $input
     * @param $code
     * @return Response
     */
    public function format($input, $code)
    {
        $serialized = is_array($input) ? $this->formatArray($input) : $this->formatString($input);

        return new Response($serialized, $code, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @param $input
     * @return string
     * @throws DataFormatException
     */
    private function formatString($input)
    {
        $decoded = json_decode($input, true);
        if ($decoded !== null) return $input;

        throw new DataFormatException('Unable to decode input');
    }

    /**
     * @param array $input
     * @return string
     */
    private function formatArray(array $input)
    {
        return json_encode($input);
    }
}