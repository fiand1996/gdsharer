<?php
/**
 * Google Core
 *
 * @date 07 June 2019
 * @version 1.0
 * @copyright Fiand T <fiand96@yahoo.com>
 * @author Fiand T (https://github.com/fiand1996)
 */
class Google
{
    private $client;

    public function __construct($AuthUser = null)
    {
        $this->client = new Google_Client();
        $this->client->setApplicationName(generalSetting("site_name"));
        $this->client->setClientId(GOOGLE_CLIENT_ID);
        $this->client->setClientSecret(GOOGLE_CLIENT_SECRET);
        $this->client->setDeveloperKey(GOOGLE_API_KEY);
        $this->client->setRedirectUri(GOOGLE_REDIRECT_URL);
        $this->client->setScopes(GOOGLE_SCOPES);
        $this->client->setAccessType('offline');
        $this->client->setApprovalPrompt('force');

        if ($AuthUser != null) {

            $this->client->setAccessToken($AuthUser->get("token"));
            if ($this->client->isAccessTokenExpired()) {
                $this->client->refreshToken($AuthUser->get("token.refresh_token"));

                $accessToken = $this->client->getAccessToken();

                $this->client->setAccessToken($accessToken);
            }
        } else {
            $accessToken = $this->client->getAccessToken();

            if ($accessToken != null) {
                $this->client->revokeToken($accessToken);
            }
        }
    }

    public function getLoginUrl()
    {
        return $this->client->createAuthUrl();
    }

    public function authenticate($code)
    {
        $this->client->authenticate($code);

        $accessToken = $this->client->getAccessToken();

        $this->client->setAccessToken($accessToken);
    }

    public function getAccessToken()
    {
        return $this->client->getAccessToken();
    }

    public function getUserInfo()
    {
        $service = new Google_Service_Oauth2($this->client);

        return $service->userinfo->get();
    }

    public function getDriveInfo()
    {
        $service = new Google_Service_Drive($this->client);

        return $service->about->Get(array(
            'fields' => '*',
        ));
    }
    
    public function downloadFile($fileId)
    {
        $service = new Google_Service_Drive($this->client);

        $response = $service->files->get($fileId, array(
            'alt' => 'media'));
        return $response->getBody()->getContents();
    }

    public function getListFiles($AuthUser, $limit = 10)
    {
        $service = new Google_Service_Drive($this->client);

        // mimeType != 'application/vnd.google-apps.spreadsheet' and
        // mimeType != 'application/vnd.google-apps.document' and
        
        return $service->files->listFiles(array(
            'q' => "mimeType != 'application/vnd.google-apps.folder' and
                    mimeType != 'application/octet-stream' and
                    mimeType != 'text/css' and
                    mimeType != 'text/html' and
                    mimeType != 'text/x-sql' and
                    mimeType != 'application/vnd.google-apps.form' and
                    '{$AuthUser->get('email')}' in writers",
            'pageSize' => $limit,
            'includeTeamDriveItems' => true,
            'supportsAllDrives' => true,
            'fields' => 'nextPageToken, files(id,name,mimeType,size,parents,webContentLink,webViewLink,hasThumbnail,thumbnailLink,createdTime)',
            'orderBy' => 'modifiedTime desc,name',
        ));
    }

    public function driveMakeCopy($id, $name = null, $public = true)
    {
        $service = new Google_Service_Drive($this->client);

        $parent = $this->driveFolderExist(DRIVE_FOLDER_NAME);

        $params = [
            'fields' => 'id',
            'parents' => array($parent),
            'description' => 'uploaded from ' . generalSetting("site_name"),
            'supportsTeamDrives' => true,
        ];

        if ($name) {
            $params['name'] = $name;
        }

        $fileMetadata = new Google_Service_Drive_DriveFile($params);

        $file = $service->files->copy(
            $id, $fileMetadata, array('fields' => '*')
        );

        if ($public) {
            $this->driveCreatePermission($file->id);
        }

        return $file;
    }

    private function driveFolderExist($folder_name)
    {
        $service = new Google_Service_Drive($this->client);

        $mydrive = $service->files->listFiles(array(
            'q' => "mimeType = 'application/vnd.google-apps.folder' and
                    name = '{$folder_name}' and
                    trashed = false",
            'fields' => '*',
            'includeTeamDriveItems' => true,
            'supportsAllDrives' => true,
        ));

        $files = $mydrive->getFiles();

        if (count($files) == 0) {
            $file = $this->driveCreateFolder($folder_name);
            $parent = $file->id;
        } else {
            $parent = $files[0]->id;
        }

        return $parent;
    }

    private function driveCreateFolder($folder_name)
    {
        $service = new Google_Service_Drive($this->client);

        $fileMetadata = new Google_Service_Drive_DriveFile(array(
            'name' => $folder_name,
            'mimeType' => 'application/vnd.google-apps.folder',
        ));

        return $service->files->create($fileMetadata, array(
            'fields' => '*',
            'supportsTeamDrives' => true,
        ));
    }

    private function driveCreatePermission($id)
    {
        $service = new Google_Service_Drive($this->client);

        $userPermission = new Google_Service_Drive_Permission(array(
            'type' => 'anyone',
            'role' => 'reader',
            'allowFileDiscovery' => true,
        ));

        return $service->permissions->create(
            $id, $userPermission, array('fields' => 'id')
        );
    }

    public function driveDelete($id)
    {
        $service = new Google_Service_Drive($this->client);

        return $service->files->delete($id, array(
            'supportsTeamDrives' => true,
        ));
    }

    public function driveDownload($id) 
    {
        $service = new Google_Service_Drive($this->client);
        return $service->files->get($id, array(
            'alt' => 'media'
        ));
    }

    public function driveEmptyTrash()
    {
        $service = new Google_Service_Drive($this->client);
        return $service->files->emptyTrash();
    }

    public function logout()
    {
        $accessToken = $this->client->getAccessToken();

        if ($accessToken != null) {
            $this->client->revokeToken($accessToken);
        }
    }
}
