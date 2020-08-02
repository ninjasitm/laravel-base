<?php

namespace Nitm\Api\Documentation\Swagger;

final class ArtistsController
{
    /**
     * @SWG\Get(
     *     path="/artist",
     *     summary="Use this endpoint to find and filter artists",
     *     tags={"artist"},
     *     description="List all artist on Octopus Artworks",
     *     operationId="allArtist",
     *     consumes={"application/json", "application/x-www-form-urlencoded"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort Artist",
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
     *             @SWG\Items(ref="#/definitions/Artist")
     *         ),
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="No Artist Found",
     *     ),
     *     security={
     *         {
     *             "artiststore_auth": {"write:artists", "read:artists"}
     *         }
     *     }
     * )
     */
    public function findAll()
    {
    }

    /**
     * @SWG\Get(
     *     path="/artist/{id}",
     *     summary="Find artist by ID",
     *     description="Returns a single artist",
     *     operationId="getArtistById",
     *     tags={"artist"},
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         description="ID of artist to return",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer",
     *         format="int64"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *         @SWG\Schema(ref="#/definitions/Artist")
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="Invalid ID supplied"
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Artist not found"
     *     ),
     *     security={
     *       {"api_key": {}},
     *       {"artiststore_auth": {"write:artists", "read:artists"}}
     *     }
     * )
     */
    public function findOne()
    {
    }
}
