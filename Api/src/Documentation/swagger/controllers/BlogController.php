<?php

namespace Nitm\Api\Documentation\Swagger;

final class PostController
{
    /**
     * @SWG\Get(
     *     path="/post",
     *     summary="Use this endpoint to find and filter posts",
     *     tags={"post"},
     *     description="List all post on Octopus posts",
     *     operationId="allPost",
     *     consumes={"application/json", "application/x-www-form-urlencoded"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="filter[category]",
     *         in="query",
     *         description="Filter by post medium",
     *         required=false,
     *         type="array",
     *         @SWG\Items(type="string"),
     *         collectionFormat="multi",
     *         enum={"painting", "lignt-installation"}
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *         @SWG\Schema(
     *             type="array",
     *             @SWG\Items(ref="#/definitions/Post")
     *         ),
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="No Post Found",
     *     ),
     *     security={
     *         {
     *             "poststore_auth": {"write:posts", "read:posts"}
     *         }
     *     }
     * )
     */
    public function findAll()
    {
    }

    /**
     * @SWG\Get(
     *     path="/post/{id}",
     *     summary="Find post by ID",
     *     description="Returns a single post",
     *     operationId="getPostById",
     *     tags={"post"},
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         description="ID of post to return",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer",
     *         format="int64"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *         @SWG\Schema(ref="#/definitions/Post")
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="Invalid ID supplied"
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Post not found"
     *     ),
     *     security={
     *       {"api_key": {}},
     *       {"poststore_auth": {"write:posts", "read:posts"}}
     *     }
     * )
     */
    public function findOne()
    {
    }

    /**
     * @SWG\Post(
     *     path="/post",
     *     tags={"post"},
     *     operationId="addPost",
     *     summary="Add a new post to the store",
     *     description="",
     *     consumes={"application/json", "application/x-www-form-urlencoded", "multippost/form-data"},
     *     produces={ "application/json"},
     *     @SWG\Parameter(
     *         name="title",
     *         type="string",
     *         in="formData",
     *         description="The title of the new post",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="content",
     *         type="string",
     *         in="formData",
     *         description="The full content of the new post",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="categories",
     *         type="array",
     *         items=@SWG\Items(type="string"),
     *         in="formData",
     *         description="The category Ids of the new post",
     *         required=false,
     *     ),
     *     @SWG\Response(
     *         response=405,
     *         description="Invalid input",
     *     ),
     *     security={{"poststore_auth":{"write:posts", "read:posts"}}}
     * )
     */
    public function createPost()
    {
    }

    /**
     * @SWG\Post(
     *     path="/post/{id}",
     *     tags={"post"},
     *     operationId="updatePost",
     *     summary="Update an existing post",
     *     description="",
     *     consumes={"application/json", "application/x-www-form-urlencoded", "multippost/form-data"},
     *     produces={ "application/json"},
     *     @SWG\Parameter(
     *         name="title",
     *         type="string",
     *         in="formData",
     *         description="The title of the new post",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="content",
     *         type="string",
     *         in="formData",
     *         description="The full content of the new post",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="categories",
     *         type="array",
     *         items=@SWG\Items(type="string"),
     *         in="formData",
     *         description="The category Ids of the new post",
     *         required=false,
     *     ),
     *     @SWG\Response(
     *         response=400,
     *         description="Invalid ID supplied",
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Post not found",
     *     ),
     *     @SWG\Response(
     *         response=405,
     *         description="Validation exception",
     *     ),
     *     security={{"poststore_auth":{"write:posts", "read:posts"}}}
     * )
     */
    public function updatePost()
    {
    }

    /**
     * @SWG\Delete(
     *     path="/post/{id}",
     *     summary="Deletes a post",
     *     description="",
     *     operationId="deletePost",
     *     consumes={"application/json", "application/x-www-form-urlencoded", "multippost/form-data"},
     *     produces={ "application/json"},
     *     tags={"post"},
     *     @SWG\Parameter(
     *         description="Post id to delete",
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
     *         description="Invalid post value"
     *     ),
     *     security={{"poststore_auth":{"write:posts", "read:posts"}}}
     * )
     */
    public function deletePost()
    {
    }
}
