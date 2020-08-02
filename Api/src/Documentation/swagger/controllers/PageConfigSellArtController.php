<?php

namespace Nitm\Api\Documentation\Swagger;

final class PageConfigSellArtController
{
    /**
     * @SWG\Get(
     *     path="/config/sell-art",
     *     summary="Use this endpoint to find and filter artwork",
     *     tags={"sellArtConfig"},
     *     description="List all art on Octopus Artworks",
     *     operationId="allSellArtConfig",
     *     consumes={"application/json", "application/x-www-form-urlencoded"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *         @SWG\Schema(
     *             type="array",
     *             @SWG\Items(ref="#/definitions/PageConfigSellArt")
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
