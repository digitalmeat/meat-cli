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
     * @var
     */
    protected $client;
    /**
     * MeatAPI constructor.
     */
    public function __construct()
    {
        $this->setClient();
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
            'headers' => [
                'Authorization' => 'Bearer ' . config('access_token', '')
            ]
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
            'form_params' => $data,
            'headers' => [
                'Authorization' => 'Bearer ' . config('access_token', '')
            ]
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
        return $this->get('projects/' . $project)['data'] ?? null;
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
        return $this->post("projects/{$project_id}/setup/bitbucket", ['code' => $code])['data'] ?? null;
    }

    /**
     * @param $project_id
     * @param $options
     * @return mixed
     */
    public function setupProjectTrello($project_id)
    {
        return $this->post("projects/{$project_id}/setup/trello")['data'] ?? null;
    }

    /**
     * @param $project_id
     * @return mixed
     */
    public function setupProjectSlack($project_id)
    {
        return $this->post("projects/{$project_id}/setup/slack")['data'] ?? null;
    }

    /**
     * @param $project_id
     * @param string $assets_scripts
     * @return mixed
     */
    public function setupProjectStaging($project_id, $assets_scripts = 'npm i && npm run production', $domain = null)
    {
        //@TODO: No enviar el comando de compilación de assets. Lo mejor sería correr el meat mount directamente en el server para asegurarnos de montarlo correctamente
        return $this->post("projects/{$project_id}/setup/forge", [
                'assets_script' => $assets_scripts,
                'domain' => $domain
        ])['data'] ?? null;
    }

    /**
     * @param $code
     * @return mixed
     */
    public function createRepository($code)
    {
        return $this->post('create-repository', [
            'code' => $code
        ]);
    }

    /**
     *
     */
    public function setClient()
    {
        $this->client = new Client([
            'base_uri' => config('api_url', 'https://api.meat.cl/api/'),
            // You can set any number of default request options.
            'timeout'  => 30.0,
            'headers' => [
                'Accept'     => 'application/json',
            ],
        ]);
    }

    public function deployNow($project_code)
    {
        return $this->post("projects/{$project_code}/deploy-now");
    }

    public function autoFindForgeSite($id, $domain = null)
    {
        return $this->post("projects/{$id}/auto-find-staging", [
            'domain' => $domain
        ]);
    }
    public function getStagingSites()
    {
        return collect($this->get('forge/staging/sites'));
    }

}