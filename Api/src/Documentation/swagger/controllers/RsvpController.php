<?php

namespace Nitm\Api\Documentation\Swagger;

final class RsvpController
{
    /**
   * @SWG\Get(
   *     path="/rsvp/{id}",
   *     summary="Use this endpoint to find and filter rsvps for an event",
   *     tags={"rsvp"},
   *     description="List all rsvps on Octopus Artworks. Will find the rsvps for the specified user ID",
   *     operationId="findRsvpByEvent",
   *     consumes={"application/json"},
   *     produces={"application/json"},
   *     @SWG\Parameter(
   *         name="id",
   *         in="path",
   *         description="The event id/name to get rsvp information for",
   *         required=true,
   *         type="string",
   *     ),
   *     @SWG\Parameter(
   *         name="sort",
   *         in="query",
   *         description="Sort Rsvps",
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
   *             @SWG\Items(ref="#/definitions/EventAttendee")
   *         ),
   *     ),
   *     @SWG\Response(
   *         response="404",
   *         description="No Rsvps Found",
   *         @SWG\Schema(
   *             type="object",
   *             @SWG\Items(ref="#/definitions/Error")
   *         ),
   *     ),
   *     security={
   *         {
   *             "rsvpstore_auth": {"write:rsvps", "read:rsvps"}
   *         }
   *     }
   * )
   */
  public function findByEventId()
  {
  }

    /**
     * @SWG\Post(
     *     path="/rsvp",
     *     tags={"rsvp"},
     *     operationId="addRsvp",
     *     summary="Add a new rsvp to the store",
     *     description="",
     *     consumes={"application/json", "application/x-www-form-urlencoded"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="event_id",
     *         type="string",
     *         in="query",
     *         description="The event",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="attendee_id",
     *         type="string",
     *         in="query",
     *         description="The user that is rsvp'ing",
     *         required=true,
     *     ),
     *     @SWG\Response(
     *         response=405,
     *         description="Invalid input",
     *     ),
     *     security={{"rsvpstore_auth":{"write:rsvps", "read:rsvps"}}}
     * )
     */
    public function createRsvp()
    {
    }

    /**
     * @SWG\Delete(
     *     path="/rsvp/{id}",
     *     summary="Deletes a rsvp",
     *     description="",
     *     operationId="deleteRsvp",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     tags={"rsvp"},
     *     @SWG\Parameter(
     *         description="Rsvp id to delete",
     *         in="path",
     *         name="event_id",
     *         required=true,
     *         type="integer",
     *         format="int64"
     *     ),
     *     @SWG\Response(
     *         response=400,
     *         description="Invalid rsvp value"
     *     ),
     *     security={{"rsvpstore_auth":{"write:rsvps", "read:rsvps"}}}
     * )
     */
    public function deleteRsvp()
    {
    }
}
