<?php

namespace Nitm\Api\Documentation\Swagger;

final class ArtController
{
    /**
     * @SWG\Get(
     *     path="/art",
     *     summary="Use this endpoint to find and filter artwork",
     *     tags={"art"},
     *     description="List all art on Octopus Artworks",
     *     operationId="allArt",
     *     consumes={"application/json", "application/x-www-form-urlencoded"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="filter[sort]",
     *         in="query",
     *         description="Sort Art",
     *         required=false,
     *         type="array",
     *         @SWG\Items(type="string"),
     *         collectionFormat="multi",
     *         enum={"asc", "desc"}
     *     ),
     *     @SWG\Parameter(
     *         name="filter[medium]",
     *         in="query",
     *         description="Filter by art medium",
     *         required=false,
     *         type="array",
     *         @SWG\Items(type="string"),
     *         collectionFormat="multi",
     *         enum={"painting", "lignt-installation"}
     *     ),
     *     @SWG\Parameter(
     *         name="filter[type]",
     *         in="query",
     *         description="Filter by art type",
     *         required=false,
     *         type="array",
     *         @SWG\Items(type="string"),
     *         collectionFormat="multi",
     *         enum={"grafitti", "fine-art"}
     *     ),
     *     @SWG\Parameter(
     *         name="filter[mood]",
     *         in="query",
     *         description="Filter by art mood",
     *         required=false,
     *         type="array",
     *         @SWG\Items(type="string"),
     *         collectionFormat="multi",
     *         enum={"playful", "happy", "loving"}
     *     ),
     *     @SWG\Parameter(
     *         name="filter[color]",
     *         in="query",
     *         description="Filter by art color",
     *         required=false,
     *         type="array",
     *         @SWG\Items(type="string"),
     *         collectionFormat="multi",
     *         enum={"red", "magenta"}
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *         @SWG\Schema(
     *             type="array",
     *             @SWG\Items(ref="#/definitions/Art")
     *         ),
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="No Art Found",
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

    /**
     * @SWG\Get(
     *     path="/art/{id}",
     *     summary="Find art by ID",
     *     description="Returns a single art",
     *     operationId="getArtById",
     *     tags={"art"},
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         description="ID of art to return",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer",
     *         format="int64"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *         @SWG\Schema(ref="#/definitions/Art")
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="Invalid ID supplied"
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Art not found"
     *     ),
     *     security={
     *       {"api_key": {}},
     *       {"artstore_auth": {"write:arts", "read:arts"}}
     *     }
     * )
     */
    public function findOne()
    {
    }

    /**
     * @SWG\Post(
     *     path="/art",
     *     tags={"art"},
     *     operationId="addArt",
     *     summary="Add a new art to the store",
     *     description="",
     *     consumes={"application/json", "application/x-www-form-urlencoded", "multipart/form-data"},
     *     produces={ "application/json"},
     *     @SWG\Parameter(
     *         name="title",
     *         type="string",
     *         in="formData",
     *         description="The title of the new art",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="image",
     *         type="object",
     *         in="formData",
     *         description="The image file of the new art",
     *         required=true,
     *         type="file",
     *     ),
     *     @SWG\Parameter(
     *         name="description",
     *         type="string",
     *         in="formData",
     *         description="The description of the new art",
     *         required=true
     *     ),
     *     @SWG\Parameter(
     *         name="mediums",
     *         type="array",
     *         @SWG\Items(type="string"),
     *         in="formData",
     *         description="The medium Ids of the new art",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="type",
     *         type="integer",
     *         in="formData",
     *         description="The type of the new art",
     *         required=true,
     *     ),
     *     @SWG\Response(
     *         response=405,
     *         description="Invalid input",
     *     ),
     *     security={{"artstore_auth":{"write:arts", "read:arts"}}}
     * )
     */
    public function createArt()
    {
    }

    /**
     * @SWG\Post(
     *     path="/art/{id}",
     *     tags={"art"},
     *     operationId="updateArt",
     *     summary="Update an existing art",
     *     description="",
     *     consumes={"application/json", "application/x-www-form-urlencoded", "multipart/form-data"},
     *     produces={ "application/json"},
     *     @SWG\Parameter(
     *         name="title",
     *         type="string",
     *         in="formData",
     *         description="The title of the new art",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="image",
     *         type="object",
     *         in="formData",
     *         description="The image file of the new art",
     *         required=true,
     *         type="file",
     *     ),
     *     @SWG\Parameter(
     *         name="description",
     *         type="string",
     *         in="formData",
     *         description="The description of the new art",
     *         required=true
     *     ),
     *     @SWG\Parameter(
     *         name="mediums",
     *         type="array",
     *         @SWG\Items(type="string"),
     *         in="formData",
     *         description="The medium Ids of the new art",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="type",
     *         type="integer",
     *         in="formData",
     *         description="The type of the new art",
     *         required=true,
     *     ),
     *     @SWG\Response(
     *         response=400,
     *         description="Invalid ID supplied",
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Art not found",
     *     ),
     *     @SWG\Response(
     *         response=405,
     *         description="Validation exception",
     *     ),
     *     security={{"artstore_auth":{"write:arts", "read:arts"}}}
     * )
     */
    public function updateArt()
    {
    }

    /**
     * @SWG\Delete(
     *     path="/art/{id}",
     *     summary="Deletes a art",
     *     description="",
     *     operationId="deleteArt",
     *     consumes={"application/json", "application/x-www-form-urlencoded", "multipart/form-data"},
     *     produces={ "application/json"},
     *     tags={"art"},
     *     @SWG\Parameter(
     *         description="Art id to delete",
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
     *         description="Invalid art value"
     *     ),
     *     security={{"artstore_auth":{"write:arts", "read:arts"}}}
     * )
     */
    public function deleteArt()
    {
    }
}
