<?php

namespace Nitm\Api\Documentation\Swagger;

final class FavoriteController
{
    /**
     * @SWG\Get(
     *     path="/favorite",
     *     summary="Use this endpoint to find and filter favorites",
     *     tags={"favorite"},
     *     description="List all favorites on Octopus Artworks",
     *     operationId="allFavorite",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort Favorites",
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
     *             @SWG\Items(ref="#/definitions/Favorite")
     *         ),
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="No Favorites Found",
     *     ),
     *     security={
     *         {
     *             "favoritestore_auth": {"write:favorites", "read:favorites"}
     *         }
     *     }
     * )
     */
    public function findAll()
    {
    }

  /**
   * @SWG\Get(
   *     path="/favorite/{id}",
   *     summary="Use this endpoint to find and filter favorites",
   *     tags={"favorite"},
   *     description="List all favorites on Octopus Artworks. Will find the favorites for the specified user ID",
   *     operationId="findFavoriteById",
   *     consumes={"application/json"},
   *     produces={"application/json"},
   *     @SWG\Parameter(
   *         name="id",
   *         in="path",
   *         description="The user id/name to get favorite information for",
   *         required=true,
   *         type="string",
   *     ),
   *     @SWG\Parameter(
   *         name="sort",
   *         in="query",
   *         description="Sort Favorites",
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
   *             @SWG\Items(ref="#/definitions/Favorite")
   *         ),
   *     ),
   *     @SWG\Response(
   *         response="404",
   *         description="No Favorites Found",
   *         @SWG\Schema(
   *             type="object",
   *             @SWG\Items(ref="#/definitions/Error")
   *         ),
   *     ),
   *     security={
   *         {
   *             "favoritestore_auth": {"write:favorites", "read:favorites"}
   *         }
   *     }
   * )
   */
  public function findByUserId()
  {
  }

    /**
     * @SWG\Post(
     *     path="/favorite",
     *     tags={"favorite"},
     *     operationId="addFavorite",
     *     summary="Add a new favorite to the store",
     *     description="",
     *     consumes={"application/json", "application/x-www-form-urlencoded"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="thing",
     *         type="string",
     *         in="query",
     *         description="The thing that you re favoriting",
     *         required=true,
     *     ),
     *     @SWG\Response(
     *         response=405,
     *         description="Invalid input",
     *     ),
     *     security={{"favoritestore_auth":{"write:favorites", "read:favorites"}}}
     * )
     */
    public function createFavorite()
    {
    }

    /**
     * @SWG\Delete(
     *     path="/favorite/{id}",
     *     summary="Deletes a favorite",
     *     description="",
     *     operationId="deleteFavorite",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     tags={"favorite"},
     *     @SWG\Parameter(
     *         description="Favorite id to delete",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer",
     *         format="int64"
     *     ),
     *     @SWG\Response(
     *         response=400,
     *         description="Invalid favorite value"
     *     ),
     *     security={{"favoritestore_auth":{"write:favorites", "read:favorites"}}}
     * )
     */
    public function deleteFavorite()
    {
    }
}
