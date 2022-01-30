<?php

namespace Nitm\Content\Configuration;

use Nitm\Content\CanJoinTeams;

trait ManagesModelOptions
{
    /**
     * The user model class name.
     *
     * @var string
     */
    public static $userModel = 'Nitm\Content\User';

    /**
     * The team model class name.
     *
     * @var string
     */
    public static $teamModel = 'Nitm\Content\Team';

    /**
     * Set the user model class name.
     *
     * @param  string $userModel
     * @return void
     */
    public static function useUserModel($userModel)
    {
        static::$userModel = $userModel;
        config(['nitm-content.user_model' => $userModel]);
    }

    /**
     * Get the user model class name.
     *
     * @return string
     */
    public static function userModel()
    {
        return config('nitm-content.user_model') ?? static::$userModel;
    }

    /**
     * Get a new user model instance.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    public static function user()
    {
        return new static::$userModel;
    }

    /**
     * Set the team model class name.
     *
     * @param  string $teamModel
     * @return void
     */
    public static function useTeamModel($teamModel)
    {
        static::$teamModel = $teamModel;
        config(['nitm-content.team_model' => $teamModel]);
    }

    /**
     * Determine if the application offers support for teams.
     *
     * @return bool
     */
    public static function usesTeams()
    {
        return in_array(CanJoinTeams::class, class_uses_recursive(static::userModel()));
    }

    /**
     * Get the team model class name.
     *
     * @return string
     */
    public static function teamModel()
    {
        return config('nitm-content.team_model') ??  static::$teamModel;
    }

    /**
     * Get a new team model instance.
     *
     * @return \Nitm\Content\Models\Team
     */
    public static function team()
    {
        return new static::$teamModel;
    }
}