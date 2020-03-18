<?php

// exec time
// set_time_limit(60 * 10);

// add your feedly API credentials
$user_id = '';
$access_token = '';


function call_api($user_id, $access_token, $continuation) {
    //
    $url = 'https://cloud.feedly.com/v3/streams/contents?streamId=user/' . $user_id . '/tag/global.saved&count=500';

    // optional continuation input
    if ($continuation) {
        $url = $url . '&continuation=' . $continuation;
    }

    // curl
    $curl = curl_init();

    // options
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Authorization: OAuth ' . $access_token,
    ));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    // curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

    // requesting saved items
    $result = curl_exec($curl);
    curl_close($curl);
    $result = json_decode($result);

    return $result;
}

// main
$items = [];
$continuation = null;
while (true) {
    $chunked = call_api($user_id, $access_token, $continuation);

    // nothing return; exit
    if (!$chunked) {
        var_dump($chunked);
        break;
    }

    // api error
    if ($chunked->errorCode ?? false) {
        var_dump($chunked);
        exit;
    }

    // success request
    if ($chunked->continuation ?? false) {
        // prepare for next request
        $continuation = $chunked->continuation;
    }
    else {
        // end of the stream
        break;
    }

    // $items = array_merge($items, $chunked->items);
    foreach ($chunked->items as $item) {
        array_push($items, $item);
    }

    // sleep, just in case
    sleep(5);
}
?>
<!DOCTYPE NETSCAPE-Bookmark-file-1>
<!-- This is an automatically generated file.
     It will be read and overwritten.
     DO NOT EDIT! -->
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<TITLE>Bookmarks</TITLE>
<!-- <H1>Bookmarks Menu</H1> -->

<DL><p>
    <DT><H3>Feedly (Total: <?php echo count($items) ?>)</H3>
    <DL><p>
        <?php foreach ($items as $item): ?>
            <DT><A HREF="<?php echo $item->canonicalUrl ?? $item->canonical[0]->href ?>" ADD_DATE="<?php echo $item->actionTimestamp ?>" LAST_MODIFIED="<?php echo $item->actionTimestamp ?>"><?php echo $item->title ?></A>
        <?php endforeach; ?>
    </DL><p>
</DL>
<!-- end of html file -->
