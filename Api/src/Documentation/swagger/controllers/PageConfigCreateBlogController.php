<?php

namespace Nitm\Api\Documentation\Swagger;

final class PageConfigCreateBlogController
{
    /**
     * @SWG\Get(
     *     path="/config/create-blog",
     *     summary="Use this endpoint to find and filter artwork",
     *     tags={"createBlogConfig"},
     *     description="Create blog posts on Octopus Artworks",
     *     operationId="allCreateBlogConfig",
     *     consumes={"application/json", "application/x-www-form-urlencoded"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *         @SWG\Schema(
     *             type="array",
     *             @SWG\Items(ref="#/definitions/PageConfigCreateBlog")
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
