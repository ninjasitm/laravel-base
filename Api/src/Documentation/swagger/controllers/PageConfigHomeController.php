<?php

namespace Nitm\Api\Documentation\Swagger;

final class PageConfigHomeController
{
    /**
     * @SWG\Get(
     *     path="/config/home",
     *     summary="Use this endpoint to find and filter the home configuration",
     *     tags={"homeConfig"},
     *     description="List all art on Octopus Artworks",
     *     operationId="homeConfig",
     *     consumes={"application/json", "application/x-www-form-urlencoded"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *         @SWG\Schema(
     *             type="array",
     *             @SWG\Items(ref="#/definitions/PageConfigHome")
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
