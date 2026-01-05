<?php

namespace App\Helpers;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Storage;

// Helper
use App\Helpers\Generator;

class Firebase
{
    private static $factory;

    public static function init()
    {
        if (!self::$factory) {
            self::$factory = (new Factory)->withServiceAccount(base_path('/firebase/wardrobe-26571-firebase-adminsdk-fint4-9966f0909b.json'));
        }
    }

    public static function uploadFile($ctx, $user_id, $username, $file, $file_ext){
        self::init();
        // Firebase Storage instance
        $storage = self::$factory->createStorage();
        $bucket = $storage->getBucket('wardrobe-26571.firebasestorage.app');
        $uploadedFile = fopen($file->getRealPath(), 'r');
        $id = Generator::getUUID();

        // Upload file to Firebase Storage
        $object = $bucket->upload($uploadedFile, [
            'name' => $ctx.'/' . $user_id . '_' . $username . '/' . $id . '.' . $file_ext,
            'predefinedAcl' => 'publicRead',
            'contentType' => $file_ext,
        ]);

        // Uploaded link
        $object->update([
            'acl' => [],
        ]);                
        $fileUrl = $object->info()['mediaLink']; 

        return $fileUrl;
    }

    public static function deleteFile($url){
        self::init();
        $storage = self::$factory->createStorage();
        $bucket = $storage->getBucket();

        $parsedUrl = parse_url($url);
        if (!isset($parsedUrl['path'])) {
            return false; 
        }

        $path = urldecode(substr($parsedUrl['path'], strpos($parsedUrl['path'], '/o/') + 3));
        $object = $bucket->object($path);

        if ($object->exists()) {
            $object->delete();
            return true; 
        }

        return false; 
    }

    public static function sendNotif($token, $msg_body, $username, $id_context = null){
        self::init();
        $messaging = self::$factory->createMessaging();
        $message = CloudMessage::withTarget('token', $token)
            ->withNotification(Notification::create("Hello $username", $msg_body))
            ->withData([
                'id_context' => $id_context,
            ]);
        $response = $messaging->send($message);
    }
}