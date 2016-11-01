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
        if ($decoded === null) throw new DataFormatException('Unable to decode input');

        return $this->formatArray($decoded);
    }

    /**
     * @param array $input
     * @return string
     */
    private function formatArray(array $input)
    {
        $output = [
            'errors' => []
        ];

        if (isset($input['error']) && is_string($input['error'])) {
            $output['errors'][] = $input['error'];
            unset($input['error']);
        }

        if (isset($input['errors']) && is_array($input['errors'])) {
            $output['errors'][] = $output['errors'] + $input['errors'];
            unset($input['errors']);
        }

        $output['data'] = $input;

        return json_encode($output);
    }
}