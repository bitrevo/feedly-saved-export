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
$bookmarks = [];
$continuation = null;
while (true) {
    $chunked = call_api($user_id, $access_token, $continuation);

    // nothing return; exit
    if (!$chunked) {
        var_dump($chunked);
        echo count($bookmarks);
        break;
    }

    // api error
    if ($chunked->errorCode ?? false) {
        var_dump($chunked);
        echo count($bookmarks);
        exit;
    }

    // success request
    // $items = array_merge($items, $chunked->items);
    foreach ($chunked->items as $item) {
        // new bookmark entry
        $bookmark = [
            'title' => $item->title,
            'href' => '',
            'add_date' => $item->actionTimestamp,
            'last_modified' => $item->actionTimestamp,
        ];
        // href cases
        if ($item->canonicalUrl ?? false) {
            $bookmark['href'] = $item->canonicalUrl;
        }
        elseif ($item->canonical[0] ?? false) {
            $bookmark['href'] = $item->canonical[0]->href;
        }
        elseif ($item->originId ?? false) {
            $bookmark['href'] = $item->originId;
        }
        elseif ($item->alternate[0] ?? false) {
            $bookmark['href'] = $item->alternate[0]->href;
        }
        else {
            // new exception
            var_dump($item);
            exit;
        }
        // add bookmark
        array_push($bookmarks, $bookmark);
    }

    // check for pagination
    if ($chunked->continuation ?? false) {
        // prepare for next request
        $continuation = $chunked->continuation;
    }
    else {
        // var_dump($chunked);
        // exit;
        // end of the stream
        break;
    }

    // sleep, just in case api rate limit
    sleep(5);
}
// exit;
?>
<!DOCTYPE NETSCAPE-Bookmark-file-1>
<!-- This is an automatically generated file.
     It will be read and overwritten.
     DO NOT EDIT! -->
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<TITLE>Bookmarks</TITLE>
<!-- <H1>Bookmarks Menu</H1> -->

<DL><p>
    <DT><H3>Feedly (Total: <?php echo count($bookmarks) ?>)</H3>
    <DL><p>
        <?php foreach ($bookmarks as $bookmark): ?>
            <DT><A HREF="<?php echo $bookmark['href'] ?>" ADD_DATE="<?php echo $bookmark['add_date'] ?>" LAST_MODIFIED="<?php echo $bookmark['last_modified'] ?>"><?php echo $bookmark['title'] ?></A>
        <?php endforeach; ?>
    </DL><p>
</DL>
<!-- end of html file -->
