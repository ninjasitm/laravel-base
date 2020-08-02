<?php

namespace Nitm\Api\Documentation\Swagger;

final class PageConfigArtistsController
{
    /**
     * @SWG\Get(
     *     path="/config/artists",
     *     summary="Use this endpoint to find and filter artwork",
     *     tags={"artistsConfig"},
     *     description="List all artists on Octopus Artworks",
     *     operationId="allArtistsConfig",
     *     consumes={"application/json", "application/x-www-form-urlencoded"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *         @SWG\Schema(
     *             type="array",
     *             @SWG\Items(ref="#/definitions/PageConfigArtists")
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
