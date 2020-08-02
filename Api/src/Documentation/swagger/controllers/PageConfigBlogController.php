<?php

namespace Nitm\Api\Documentation\Swagger;

final class PageConfigBlogController
{
    /**
     * @SWG\Get(
     *     path="/config/blog",
     *     summary="Use this endpoint to find and filter artwork",
     *     tags={"blogConfig"},
     *     description="List all blog posts on Octopus Artworks",
     *     operationId="allBlogConfig",
     *     consumes={"application/json", "application/x-www-form-urlencoded"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *         @SWG\Schema(
     *             type="array",
     *             @SWG\Items(ref="#/definitions/PageConfigBlog")
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
