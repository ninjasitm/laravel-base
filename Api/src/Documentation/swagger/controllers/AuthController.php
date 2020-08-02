<?php

namespace Nitm\Api\Documentation\Swagger;

final class AuthController
{
    /**
     * @SWG\Post(
     *     path="/auth/register",
     *     tags={"auth"},
     *     operationId="registerAccount",
     *     summary="Register a new user account",
     *     description="",
     *     consumes={"application/json", "application/x-www-form-urlencoded", "multipuser/form-data"},
     *     produces={ "application/json"},
     *     @SWG\Parameter(
     *         name="username",
     *         type="string",
     *         in="formData",
     *         description="The username/login of the new user",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="password",
     *         type="string",
     *         in="formData",
     *         description="The user's password",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="password_confirmation",
     *         type="string",
     *         in="formData",
     *         description="The password confirmatio",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="email",
     *         type="string",
     *         in="formData",
     *         description="The email of the new user",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="name",
     *         type="string",
     *         in="formData",
     *         description="The display name of the new user",
     *         required=false,
     *     ),
     *     @SWG\Response(
     *         response=405,
     *         description="Invalid input",
     *     ),
     *     security={{"userstore_auth":{"write:users", "read:users"}}}
     * )
     */
    public function registerAccount()
    {
    }

    /**
     * @SWG\Post(
     *     path="/auth/login",
     *     tags={"auth"},
     *     operationId="loginAccount",
     *     summary="Login to a user account",
     *     description="",
     *     consumes={"application/json", "application/x-www-form-urlencoded", "multipart/form-data"},
     *     produces={ "application/json"},
     *     @SWG\Parameter(
     *         name="login",
     *         in="formData",
     *         type="string",
     *         description="The username/email to use for authentication",
     *         required=false,
     *     ),
     *     @SWG\Parameter(
     *         name="password",
     *         in="formData",
     *         type="string",
     *         description="UThe password to use for authentication",
     *         required=false,
     *     ),
     *     @SWG\Response(
     *         response=400,
     *         description="Invalid ID supplied",
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="User not found",
     *     ),
     *     @SWG\Response(
     *         response=405,
     *         description="Validation exception",
     *     ),
     *     security={{"userstore_auth":{"write:users", "read:users"}}}
     * )
     */
    public function loginAccount()
    {
    }

    /**
     * @SWG\Post(
     *     path="/auth/logout",
     *     tags={"auth"},
     *     operationId="loginAccount",
     *     summary="Logout of the current session",
     *     description="",
     *     consumes={"application/json"},
     *     produces={ "application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="Successfully logged out",
     *     ),
     *     security={{"userstore_auth":{"write:users", "read:users"}}}
     * )
     */
    public function logoutAccount()
    {
    }

    /**
     * @SWG\Post(
     *     path="/auth/update/{id}",
     *     tags={"auth"},
     *     operationId="updateAccount",
     *     summary="Update an existing user. This is tied to the currently logged in user",
     *     description="",
     *     consumes={"application/json", "application/x-www-form-urlencoded", "multipart/form-data"},
     *     produces={ "application/json"},
     *     @SWG\Parameter(
     *         name="username",
     *         type="string",
     *         in="formData",
     *         description="The username/login of the new user",
     *         required=false,
     *     ),
     *     @SWG\Parameter(
     *         name="password",
     *         type="string",
     *         in="formData",
     *         description="The user's password",
     *         required=false,
     *     ),
     *     @SWG\Parameter(
     *         name="password_confirmation",
     *         type="string",
     *         in="formData",
     *         description="The password confirmatio",
     *         required=false,
     *     ),
     *     @SWG\Parameter(
     *         name="email",
     *         type="string",
     *         in="formData",
     *         description="The email of the new user",
     *         required=false,
     *     ),
     *     @SWG\Parameter(
     *         name="name",
     *         type="string",
     *         in="formData",
     *         description="The display name of the new user",
     *         required=false,
     *     ),
     *     @SWG\Parameter(
     *         name="avatar",
     *         type="string",
     *         in="formData",
     *         description="The optional avatar for the usert",
     *         required=false,
     *         type="file",
     *     ),
     *     @SWG\Response(
     *         response=400,
     *         description="Invalid ID supplied",
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="User not found",
     *     ),
     *     @SWG\Response(
     *         response=405,
     *         description="Validation exception",
     *     ),
     *     security={{"userstore_auth":{"write:users", "read:users"}}}
     * )
     */
    public function updateAccount()
    {
    }

    /**
     * @SWG\Post(
     *     path="/auth/restore/{id}",
     *     tags={"auth"},
     *     operationId="restoreAccount",
     *     summary="Restore a deleted user",
     *     description="",
     *     consumes={"application/json", "application/x-www-form-urlencoded", "multipart/form-data"},
     *     produces={ "application/json"},
     *     @SWG\Parameter(
     *         name="id",
     *         type="string",
     *         in="formData",
     *         description="The id of the user to restore",
     *         required=false,
     *     ),
     *     @SWG\Parameter(
     *         name="password",
     *         type="string",
     *         in="formData",
     *         description="The user's password",
     *         required=true,
     *     ),
     *     @SWG\Response(
     *         response=400,
     *         description="Invalid ID supplied",
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="User not found",
     *     ),
     *     @SWG\Response(
     *         response=405,
     *         description="Validation exception",
     *     ),
     *     security={{"userstore_auth":{"write:users", "read:users"}}}
     * )
     */
    public function restoreAccount()
    {
    }

    /**
     * @SWG\Delete(
     *     path="/auth/deactivate/{id}",
     *     summary="Deactivates a user",
     *     description="",
     *     operationId="deactivateAccount",
     *     consumes={"application/json", "application/x-www-form-urlencoded", "multipart/form-data"},
     *     produces={ "application/json"},
     *     tags={"auth"},
     *     @SWG\Parameter(
     *         description="User id to deactivate",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer",
     *         format="int64"
     *     ),
     *     @SWG\Parameter(
     *         name="password",
     *         type="string",
     *         in="formData",
     *         description="The user's password",
     *         required=true,
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
     *         description="Invalid user value"
     *     ),
     *     security={{"userstore_auth":{"write:users", "read:users"}}}
     * )
     */
    public function deactivateAccount()
    {
    }

    /**
     * @SWG\Delete(
     *     path="/auth/delete/{id}",
     *     summary="Deletes a user",
     *     description="",
     *     operationId="deleteAccount",
     *     consumes={"application/json", "application/x-www-form-urlencoded", "multipart/form-data"},
     *     produces={ "application/json"},
     *     tags={"auth"},
     *     @SWG\Parameter(
     *         description="User id to delete",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer",
     *         format="int64"
     *     ),
     *     @SWG\Parameter(
     *         name="password",
     *         type="string",
     *         in="formData",
     *         description="The user's password",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="auth",
     *         in="header",
     *         description="The admin API key",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=400,
     *         description="Invalid user"
     *     ),
     *     security={{"userstore_auth":{"write:users", "read:users"}}}
     * )
     */
    public function deleteAccount()
    {
    }
}
