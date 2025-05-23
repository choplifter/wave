<?php

namespace Laravel\Socialite\Two;

use Illuminate\Support\Arr;

class TeslaProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * The base Tesla API URL.
     *
     * @var string
     */
    protected $baseUrl = 'https://fleet-auth.prd.vn.cloud.tesla.com/oauth2/v3';
    protected $apiUrl = 'https://fleet-api.prd.eu.vn.cloud.tesla.com/api/1';

    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = ['openid offline_access user_data vehicle_device_data vehicle_cmds vehicle_charging_cmds'];
    
    /**
     * Get the authentication URL for Tesla.
     *
     * @param  string  $state
     * @return string
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase("{$this->baseUrl}/authorize", $state);
    }

    /**
     * Get the token URL for Tesla.
     *
     * @return string
     */
    protected function getTokenUrl()
    {
        return "{$this->baseUrl}/token";
    }

    /**
     * Get the user by token.
     *
     * @param  string  $token
     * @return array
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get("{$this->apiUrl}/users/me", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * Map the raw user array to a Socialite User instance.
     *
     * @param  array  $user
     * @return \Laravel\Socialite\Two\User
     */
    protected function mapUserToObject(array $user)
    {
        return (new User())->setRaw($user)->map([
            'id' => $user['response']['vault_uuid'] ?? null,
            'nickname' => null,
            'name' => $user['response']['full_name'] ?? null,
            'email' => $user['response']['email'] ?? null,
            'avatar' => $user['response']['profile_image_url'] ?? null,
        ]);
    }
}