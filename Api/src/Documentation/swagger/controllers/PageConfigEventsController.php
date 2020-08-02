<?php

namespace Nitm\Api\Documentation\Swagger;

final class PageConfigEventsController
{
    /**
     * @SWG\Get(
     *     path="/config/events",
     *     summary="Use this endpoint to find and filter artwork",
     *     tags={"eventsConfig"},
     *     description="List all eventson Octopus Artworks",
     *     operationId="allEventsConfig",
     *     consumes={"application/json", "application/x-www-form-urlencoded"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *         @SWG\Schema(
     *             type="array",
     *             @SWG\Items(ref="#/definitions/PageConfigEvents")
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
