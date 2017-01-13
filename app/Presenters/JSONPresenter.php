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
     * @param $input
     * @return array
     */
    public static function safeDecode($input) {
        // Fix for PHP's issue with empty objects
        $input = preg_replace('/{\s*}/', "{\"EMPTY_OBJECT\":true}", $input);

        return json_decode($input, true);
    }

    /**
     * @param array|object $input
     * @return string
     */
    public static function safeEncode($input) {
        return preg_replace('/{"EMPTY_OBJECT"\s*:\s*true}/', '{}', json_encode($input, JSON_UNESCAPED_SLASHES));
    }

    /**
     * @param array|string $input
     * @param $code
     * @return Response
     */
    public function format($input, $code)
    {
        if (empty($input) && ! is_array($input)) return new Response(null, $code);

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
        $decoded = self::safeDecode($input);
        if ($decoded === null) throw new DataFormatException('Unable to decode input');

        return $this->formatArray($decoded);
    }

    /**
     * @param array $input
     * @return string
     */
    private function formatArray(array $input)
    {
        $output = [];

        if (isset($input['error']) && is_string($input['error'])) {
            $output['errors'] = [ $input['error'] ];
            unset($input['error']);
        }

        if (isset($input['errors']) && is_array($input['errors'])) {
            $output['errors'] = $input['errors'];
            unset($input['errors']);
        }

        $output['data'] = $input;

        return self::safeEncode($output);
    }
}