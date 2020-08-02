<?php

namespace Nitm\Api\Documentation\Swagger;

final class EventController
{
    /**
     * @SWG\Get(
     *     path="/event",
     *     summary="Use this endpoint to find and filter events",
     *     tags={"event"},
     *     description="List all events on Octopus Artworks",
     *     operationId="allEvent",
     *     consumes={"application/json", "application/x-www-form-urlencoded"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort Event",
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
     *             @SWG\Items(ref="#/definitions/Event")
     *         ),
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="No Events Found",
     *     ),
     *     security={
     *         {
     *             "eventstore_auth": {"write:events", "read:events"}
     *         }
     *     }
     * )
     */
    public function findAll()
    {
    }

    /**
     * @SWG\Get(
     *     path="/event/{id}",
     *     summary="Find event by ID",
     *     description="Returns a single event",
     *     operationId="getEventById",
     *     tags={"event"},
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         description="ID of event to return",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer",
     *         format="int64"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *         @SWG\Schema(ref="#/definitions/Event")
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="Invalid ID supplied"
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Event not found"
     *     ),
     *     security={
     *       {"api_key": {}},
     *       {"eventstore_auth": {"write:events", "read:events"}}
     *     }
     * )
     */
    public function findOne()
    {
    }

    /**
     * @SWG\Post(
     *     path="/event",
     *     tags={"event"},
     *     operationId="addEvent",
     *     summary="Add a new event to the store",
     *     description="",
     *     consumes={"application/json", "application/x-www-form-urlencoded", "multipevent/form-data"},
     *     produces={ "application/json"},
     *     @SWG\Parameter(
     *         name="title",
     *         type="string",
     *         in="formData",
     *         description="The title of the new event",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="image",
     *         type="string",
     *         in="formData",
     *         description="The image file of the new event",
     *         required=true,
     *         type="file",
     *     ),
     *     @SWG\Parameter(
     *         name="description",
     *         type="string",
     *         in="formData",
     *         description="The description of the new event",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="category",
     *         type="integer",
     *         in="formData",
     *         description="The category of the new event",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="type",
     *         type="integer",
     *         in="formData",
     *         description="The type of the new event",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="status",
     *         type="integer",
     *         in="formData",
     *         description="The status of the new event",
     *         required=true,
     *         enum={"normal", "postponed", "canceled"},
     *     ),
     *     @SWG\Parameter(
     *         name="starts_at",
     *         type="string",
     *         in="formData",
     *         description="The start time of the new event",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="ends_at",
     *         type="string",
     *         in="formData",
     *         description="The end time of the new event",
     *     ),
     *     @SWG\Parameter(
     *         name="is_free",
     *         type="boolean",
     *         in="formData",
     *         description="Is this a free event",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="cost",
     *         type="number",
     *         in="formData",
     *         description="The cost of the new event",
     *     ),
     *     @SWG\Response(
     *         response=405,
     *         description="Invalid input",
     *     ),
     *     security={{"eventstore_auth":{"write:events", "read:events"}}}
     * )
     */
    public function createEvent()
    {
    }

    /**
     * @SWG\Post(
     *     path="/event/{id}",
     *     tags={"event"},
     *     operationId="updateEvent",
     *     summary="Update an existing event",
     *     description="",
     *     consumes={"application/json", "application/x-www-form-urlencoded", "multipevent/form-data"},
     *     produces={ "application/json"},
     *     @SWG\Parameter(
     *         name="title",
     *         type="string",
     *         in="formData",
     *         description="The title of the new event",
     *         required=false,
     *     ),
     *     @SWG\Parameter(
     *         name="image",
     *         type="string",
     *         in="formData",
     *         description="The image file of the new event",
     *         required=false,
     *         type="file",
     *     ),
     *     @SWG\Parameter(
     *         name="description",
     *         type="string",
     *         in="formData",
     *         description="The description of the new event",
     *         required=false,
     *     ),
     *     @SWG\Parameter(
     *         name="category",
     *         type="array",
     *         items=@SWG\Items(type="string"),
     *         in="formData",
     *         description="The category of the new event",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="type",
     *         type="integer",
     *         in="formData",
     *         description="The type of the new event",
     *         required=true,
     *     ),
     *     @SWG\Response(
     *         response=400,
     *         description="Invalid ID supplied",
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Event not found",
     *     ),
     *     @SWG\Response(
     *         response=405,
     *         description="Validation exception",
     *     ),
     *     security={{"eventstore_auth":{"write:events", "read:events"}}}
     * )
     */
    public function updateEvent()
    {
    }

    /**
     * @SWG\Delete(
     *     path="/event/{id}",
     *     summary="Deletes a event",
     *     description="",
     *     operationId="deleteEvent",
     *     consumes={"application/json", "application/x-www-form-urlencoded", "multipevent/form-data"},
     *     produces={ "application/json"},
     *     tags={"event"},
     *     @SWG\Parameter(
     *         description="Event id to delete",
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
     *         description="Invalid event value"
     *     ),
     *     security={{"eventstore_auth":{"write:events", "read:events"}}}
     * )
     */
    public function deleteEvent()
    {
    }
}
