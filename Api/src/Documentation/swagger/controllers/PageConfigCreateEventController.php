<?php

namespace Nitm\Api\Documentation\Swagger;

final class PageConfigCreateEventController
{
    /**
     * @SWG\Get(
     *     path="/config/create-event",
     *     summary="Use this endpoint to find and filter artwork",
     *     tags={"createEventConfig"},
     *     description="Create Events on Octopus Artworks",
     *     operationId="allCreateEventConfig",
     *     consumes={"application/json", "application/x-www-form-urlencoded"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *         @SWG\Schema(
     *             type="array",
     *             @SWG\Items(ref="#/definitions/PageConfigCreateEvent")
     *         ),
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="No config Found",
     *     ),
     *     security={
     *         {
     *             "artstore_auth": {"write:arts", "read:arts"}
     *         }
     *     }
     * )
     */
    public function findAll()
    {
    }
}
