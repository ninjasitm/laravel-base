<?php

namespace Nitm\Api\Documentation\Swagger;

final class PageConfigArtController
{
    /**
     * @SWG\Get(
     *     path="/config/art",
     *     summary="Use this endpoint to find and filter artwork",
     *     tags={"artConfig"},
     *     description="List all art on Octopus Artworks",
     *     operationId="allArtConfig",
     *     consumes={"application/json", "application/x-www-form-urlencoded"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *         @SWG\Schema(
     *             type="array",
     *             @SWG\Items(ref="#/definitions/PageConfigArt")
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
