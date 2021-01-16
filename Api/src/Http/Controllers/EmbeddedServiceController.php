<?php

namespace Nitm\Api\Http\Controllers;

/**
 * Generate an embedded servce view
 */
class EmbeddedServiceController extends Controller
{
    public function index($service)
    {
        try {
            return view(
                "embedded/{$service}", [
                'src' => "/$service?".http_build_query($_GET)
                ]
            );
        } catch(\Exception $e) {
            abort(404, "Service [{$service}] not found");
        }
    }
}