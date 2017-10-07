<?php

namespace Meat\Cli\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

/**
 * Class MeatAPI
 * @package Meat\Cli\Helpers
 */
class MeatAPI
{
    /**
     * MeatAPI constructor.
     */
    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => config('api_url', 'https://api.meat.cl/'),
            // You can set any number of default request options.
            'timeout'  => 30.0,
            'headers' => [
                'Authorization' => 'Bearer ' . config('access_token', ''),
                'Accept'     => 'application/json',
            ],
        ]);

    }

    /**
     * @param $uri
     * @param array $data
     * @return mixed
     */
    public function get($uri, $data = [])
    {
        $response = $this->client->get($uri, [
            'query' => $data,
        ]);

        return  json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param $uri
     * @param array $data
     * @return mixed
     */
    public function post($uri, $data = [])
    {
        $response = $this->client->post($uri, [
            'form_params' => $data
        ]);
        $content = $response->getBody()->getContents();
        return json_decode($content, true);
    }

    /**
     * @param $user
     * @param $pass
     * @return mixed
     */
    public function login($user, $pass)
    {

        return $this->post('meat-cli/authorize', [
            'email' => $user,
            'password' => $pass,
            'hostname' => gethostname()
        ]);


    }

    /**
     * @return mixed
     */
    public function me()
    {
        return $this->get('me');
    }

    /**
     * @param $project
     * @return mixed
     */
    public function notifyProjectInstallation($project)
    {
        return $this->post('projects/installation', ['project' => $project, 'hostname' => gethostname()]);
    }

    /**
     * @return mixed
     */
    public function listUsers()
    {
        return $this->get('users');
    }

    /**
     * @param $project string Project ID or Code
     * @return mixed
     */
    public function getProject($project)
    {
        return $this->get('projects/' . $project);
    }

    /**
     * @param $project_code
     * @param $project_name
     * @param $project_type
     * @return mixed
     */
    public function createProject($project_code, $project_name, $project_type)
    {
        return $this->post('projects', [
            'code' => $project_code,
            'name' => $project_name,
            'type' => $project_type
        ]);
    }

    /**
     * @param $project_id
     * @return mixed
     */
    public function setupProjectBitbucket($project_id, $code = null)
    {
        return $this->post("projects/{$project_id}/setup/bitbucket", ['code' => $code]);
    }

    /**
     * @param $project_id
     * @param $options
     * @return mixed
     */
    public function setupProjectTrello($project_id)
    {
        return $this->post("projects/{$project_id}/setup/trello");
    }

    /**
     * @param $project_id
     * @param $options
     * @return mixed
     */
    public function setupProjectSlack($project_id)
    {
        return $this->post("projects/{$project_id}/setup/slack");
    }

    /**
     * @param $project_id
     * @param $options
     * @return mixed
     */
    public function setupProjectForge($project_id, $assets_scripts = 'npm i && npm run production')
    {
        //@TODO: No enviar el comando de compilación de assets. Lo mejor sería correr el meat mount directamente en el server para asegurarnos de montarlo correctamente
        return $this->post("projects/{$project_id}/setup/forge", [
            'assets_script' => $assets_scripts
        ]);
    }

    public function createRepository($code)
    {
        return $this->post('create-repository', [
            'code' => $code
        ]);
    }

}