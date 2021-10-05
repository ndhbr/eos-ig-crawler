<?php
require __DIR__ . '/vendor/autoload.php';

$users = [];
$maxMedias = 200;

if(!empty($_POST['users']) && !empty($_POST['max-medias'])) {
    $users = $_POST['users'];
    $maxMedias = $_POST['max-medias'];
}

if(!empty($users) && !empty($maxMedias) && is_numeric($maxMedias)) {
    $instagram = new \InstagramScraper\Instagram();
    $accounts = explode(',', $users);
    $result = [];
    
    foreach($accounts as $account) {
        $user = $instagram->getAccount($account);
        $medias = $instagram->getMedias($account, $maxMedias);
    
        foreach($medias as $media) {
            $beitrag = [];
    
            $dateAbfrage = new DateTime(null, new DateTimeZone('Europe/Amsterdam'));
            $dateUpload = date('d-m-Y H:i:s', $media->getCreatedTime());

            $beitrag['Abfragezeit'] = $dateAbfrage->format('d-m-Y H:i:s');
            $beitrag['Uploadzeitpunkt'] = $dateUpload;
            $beitrag['Name des Profils'] = $user->getUsername();
            $beitrag['Anzahl der Follower'] = $user->getFollowedByCount();
            $beitrag['Anzahl der Gefolgten'] = $user->getFollowsCount();
            $beitrag['Anzahl der Uploads'] = $user->getMediaCount();
            $beitrag['URL des Beitrags'] = $media->getLink();
            $beitrag['Anzahl der Likes'] = $media->getLikesCount();
            $beitrag['Anzahl der Kommentare'] = $media->getCommentsCount();
            $beitrag['Bildbeschreibung als Fließtext'] = $media->getCaption();
    
            array_push($result, $beitrag);
        }
    }
    
    function array2csv(array &$array)
    {
        if (count($array) == 0) {
            return null;
        }
    
        ob_start();
        
        $df = fopen("php://output", 'w');
        fputcsv($df, array_keys(reset($array)));
        
        foreach ($array as $row) {
            fputcsv($df, $row);
        }
        
        fclose($df);
        return ob_get_clean();
    }
    
    function download_send_headers($filename) {
        // disable caching
        $now = gmdate("D, d M Y H:i:s");
        header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
        header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
        header("Last-Modified: {$now} GMT");
    
        // force download  
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
    
        // disposition / encoding on response body
        header("Content-Disposition: attachment;filename={$filename}");
        header("Content-Transfer-Encoding: binary");
    }
    
    download_send_headers("edgesofexport_" . date("Y-m-d") . ".csv");
    echo array2csv($result);
    die();
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">

    <title>EOSinator</title>
    <meta name="description" content="EOSinator">
    <meta name="author" content="Andreas Huber">
</head>
<body>
    <form method="post">
        <input type="text" name="users" size="64" placeholder="Komma separierte Benutzernamen" />
        <input type="text" name="max-medias" size="8" placeholder="Max Media Count" value="<?php echo $maxMedias; ?>" />
        <input type="submit" value="Gogo Gadget" />
    </form>
</body>
</html>