<?php

namespace Nitm\Api\Documentation\Swagger;

final class FollowController
{
    /**
     * @SWG\Get(
     *     path="/follow",
     *     summary="Use this endpoint to find and filter follows",
     *     tags={"follow"},
     *     description="List all follows on Octopus Artworks",
     *     operationId="allFollow",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort Follows",
     *         required=false,
     *         type="array",
     *         @SWG\Items(type="string"),
     *         collectionFormat="multi",
     *         enum={"asc", "desc"}
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *         @SWG\Schema(
     *             type="array",
     *             @SWG\Items(ref="#/definitions/Follow")
     *         ),
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="No Follows Found",
     *     ),
     *     security={
     *         {
     *             "followstore_auth": {"write:follows", "read:follows"}
     *         }
     *     }
     * )
     */
    public function findAll()
    {
    }

  /**
   * @SWG\Get(
   *     path="/follow/{id}",
   *     summary="Use this endpoint to find and filter follows",
   *     tags={"follow"},
   *     description="List all follows on Octopus Artworks. Will find the follows for the specified user ID",
   *     operationId="findFollowById",
   *     consumes={"application/json"},
   *     produces={"application/json"},
   *     @SWG\Parameter(
   *         name="id",
   *         in="path",
   *         description="The user id/name to get follow information for",
   *         required=true,
   *         type="string",
   *     ),
   *     @SWG\Parameter(
   *         name="sort",
   *         in="query",
   *         description="Sort Follows",
   *         required=false,
   *         type="array",
   *         @SWG\Items(type="string"),
   *         collectionFormat="multi",
   *         enum={"asc", "desc"}
   *     ),
   *     @SWG\Response(
   *         response=200,
   *         description="successful operation",
   *         @SWG\Schema(
   *             type="array",
   *             @SWG\Items(ref="#/definitions/Follow")
   *         ),
   *     ),
   *     @SWG\Response(
   *         response="404",
   *         description="No Follows Found",
   *         @SWG\Schema(
   *             type="object",
   *             @SWG\Items(ref="#/definitions/Error")
   *         ),
   *     ),
   *     security={
   *         {
   *             "followstore_auth": {"write:follows", "read:follows"}
   *         }
   *     }
   * )
   */
  public function findByUserId()
  {
  }

    /**
     * @SWG\Post(
     *     path="/follow",
     *     tags={"follow"},
     *     operationId="addFollow",
     *     summary="Add a new follow to the store",
     *     description="",
     *     consumes={"application/json", "application/x-www-form-urlencoded"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="followee",
     *         type="string",
     *         in="query",
     *         description="The user that is performing the follow. Most generally the logged in user",
     *         required=true,
     *     ),
     *     @SWG\Response(
     *         response=405,
     *         description="Invalid input",
     *     ),
     *     security={{"followstore_auth":{"write:follows", "read:follows"}}}
     * )
     */
    public function createFollow()
    {
    }

    /**
     * @SWG\Delete(
     *     path="/follow/{id}",
     *     summary="Deletes a follow",
     *     description="",
     *     operationId="deleteFollow",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     tags={"follow"},
     *     @SWG\Parameter(
     *         description="Follow id to delete",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer",
     *         format="int64"
     *     ),
     *     @SWG\Parameter(
     *         name="api_key",
     *         in="header",
     *         description="",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=400,
     *         description="Invalid follow value"
     *     ),
     *     security={{"followstore_auth":{"write:follows", "read:follows"}}}
     * )
     */
    public function deleteFollow()
    {
    }
}
