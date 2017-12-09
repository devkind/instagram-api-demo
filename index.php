<?php

set_time_limit(0);
ini_set('default_socket_timeout',300);
session_start();

define('CLIENT_ID','YOUR-CLIENT-ID');
define('CLIENT_SECRET','YOUR-CLIENT-SECRET');

define('REDIRECT_URI','USE-HTTP-URL');
define('imageDirectory','pics/');

//connect with Instagram

  function connectToInstagram($url){
    $ch = curl_init();

    curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false
    ));

    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}

//get instagram user ID
function getUserID($accesstoken)
{

    $url = 'https://api.instagram.com/v1/users/self?access_token='.$accesstoken;

    $instagramInfo = connectToInstagram($url);
    $results = json_decode($instagramInfo,true);

    return $results['data']['id'];
}

//print the images on screen
function printImages($token)
{
    $url = 'https://api.instagram.com/v1/users/self/media/recent/?access_token='.$token;
    $instagramInfo = connectToInstagram($url);
    $results = json_decode($instagramInfo,true);
    foreach($results['data'] as $items)
        {
            $image_url = $items['images']['low_resolution']['url'];
            savePicture($image_url);
            echo '<img src = "'.$image_url.'"/><br/>';
        }
}

function savePicture($imageUrl)
{
    $imageUrl = explode('?',$imageUrl);
    $newImgUrl = $imageUrl[0];
    $filename = basename($newImgUrl);
    echo $filename . '<br/>';
    $destination = imageDirectory.$filename;
    file_put_contents($destination,file_get_contents($newImgUrl));
}

if(isset($_GET['code']))
    {
        $code = $_GET['code'];
        $url = "https://api.instagram.com/oauth/access_token";

        $access_token_settings = array(
            'client_id'  =>  CLIENT_ID,
            'client_secret' =>  CLIENT_SECRET,
            'grant_type'    =>  'authorization_code',
            'redirect_uri'  =>  REDIRECT_URI,
            'code'  =>  $code
        );
    $curl = curl_init($url);
    curl_setopt($curl,CURLOPT_POST,true);
    curl_setopt($curl,CURLOPT_POSTFIELDS,$access_token_settings);
    curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,false);

    $result = curl_exec($curl);
    curl_close($curl);

    $results = json_decode($result,true);
    $access_token = $results['access_token'];
    $uname = $results['user']['username'];
    echo $uname;
    $uid = getUserID($access_token);
    printImages($access_token);
    }
else
    {?>

        <!DOCTYPE html>
        <html>
            <head>
            </head>

            <body>
            <a href = "https://api.instagram.com/oauth/authorize/?client_id=<?php echo CLIENT_ID; ?>&redirect_uri=<?php echo REDIRECT_URI; ?>&response_type=code">Login With Instagram</a>
            </body>
        </html>

   <?php }

?>
