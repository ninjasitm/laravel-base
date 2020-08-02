<?php

namespace Nitm\Api\Documentation\Swagger;

final class FeedController
{
    /**
     * @SWG\Get(
     *     path="/feed",
     *     summary="Use this endpoint to find and filter the activity feed",
     *     tags={"feed"},
     *     description="List activity feed on Octopus Artworks",
     *     operationId="allFeed",
     *     consumes={"application/json", "application/x-www-form-urlencoded"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="filter[sort]",
     *         in="query",
     *         description="Sort Feed",
     *         required=false,
     *         type="array",
     *         @SWG\Items(type="string"),
     *         collectionFormat="multi",
     *         enum={"asc", "desc"}
     *     ),
     *     @SWG\Parameter(
     *         name="filter[action]",
     *         in="query",
     *         description="Filter Feed by activity type",
     *         required=false,
     *         type="array",
     *         @SWG\Items(type="string"),
     *         collectionFormat="multi",
     *         enum={"create", "join"}
     *     ),
     *     @SWG\Parameter(
     *         name="filter[new]",
     *         in="query",
     *         description="Filter Feed by activity type",
     *         required=false,
     *         type="array",
     *         @SWG\Items(type="string"),
     *         collectionFormat="multi",
     *         enum={"true"}
     *     ),
     *     @SWG\Parameter(
     *         name="filter[featured]",
     *         in="query",
     *         description="Filter Feed by activity type",
     *         required=false,
     *         type="array",
     *         @SWG\Items(type="string"),
     *         collectionFormat="multi",
     *         enum={"true"}
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *         @SWG\Schema(
     *             type="array",
     *             @SWG\Items(ref="#/definitions/Feed")
     *         ),
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="No Feed Found",
     *     ),
     *     security={
     *         {
     *             "feedstore_auth": {"write:feeds", "read:feeds"}
     *         }
     *     }
     * )
     */
    public function findAll()
    {
    }

    /**
     * @SWG\Get(
     *     path="/feed/{id}",
     *     summary="Find feed by ID",
     *     description="Returns a single feed for the specified user id",
     *     operationId="getFeedById",
     *     tags={"feed"},
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         description="USer ID of feed to return",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer",
     *         format="int64"
     *     ),
     *     @SWG\Parameter(
     *         name="activity",
     *         in="query",
     *         description="Filter Feed by activity type",
     *         required=false,
     *         type="array",
     *         @SWG\Items(type="string"),
     *         collectionFormat="multi",
     *         enum={"create", "join"}
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *         @SWG\Schema(ref="#/definitions/Feed")
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="Invalid ID supplied"
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Feed not found"
     *     ),
     *     security={
     *       {"api_key": {}},
     *       {"feedstore_auth": {"write:feeds", "read:feeds"}}
     *     }
     * )
     */
    public function findByUserId()
    {
    }
}
