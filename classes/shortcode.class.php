<?php

class Shortcode
{

    public function get_feed($hex)
    {
        global $wpdb;
        $table = "{$wpdb->prefix}yt_feed";
        $query = "SELECT * FROM $table WHERE hex = '$hex'";

        $datos = $wpdb->get_results($query, ARRAY_A);
        if (empty($datos)) {
            $datos = array();
        }

        return $datos;
    }

    public function calling_posts($hex)
    {
        $feed_data = $this->get_feed($hex);

        if (!empty($feed_data)) {
            $feed_data = $feed_data[0];
            $channel_id = $feed_data['channel_id'];
            $google_key = $feed_data['google_key'];

            // Paginación inicial
            $this->load_more_posts($channel_id, $google_key, '');
        } else {
            echo 'No se encontraron datos para el feed ID proporcionado';
        }
    }

    public function load_more_posts($channel_id, $google_key, $page_token)
    {
        $nro_post_to_show = 9;
        $url = "https://www.googleapis.com/youtube/v3/playlistItems?part=snippet,contentDetails&maxResults=$nro_post_to_show&playlistId=$channel_id&key=$google_key";

        if (!empty($page_token)) {
            $url .= "&pageToken=$page_token";
        }

        $response = file_get_contents($url);

        if ($response !== FALSE) {
            $data = json_decode($response, true);

            echo '<input type="hidden" value="'.$channel_id.'" name="channel_id">';
            echo '<input type="hidden" value="'.$google_key.'" name="google_key">';
            $this->get_posts($data);
            
        } else {
            echo 'Error al realizar la solicitud';
        }
    }

    public function get_posts($data, $action = 1)
    {
        if (isset($data['items']) && is_array($data['items'])) {
            $nextPageToken = $data['nextPageToken'] ?? '';

            if($action == 1){
                echo '<div class="frm_yt_feed">
                <div class="frm_yt_grid">';
            }

            foreach ($data['items'] as $item) {
                if (isset($item['contentDetails']['videoId'])) {
                    $videoId = $item['contentDetails']['videoId'];
                } else {
                    $videoId = '';
                }

                $title = $item['snippet']['title'];
                $description = $item['snippet']['description'];
                $publishedAt = $item['snippet']['publishedAt'];

                $thumbnailUrl = $item['snippet']['thumbnails']['maxres']['url'] ?? $item['snippet']['thumbnails']['high']['url'];

                $dateTime = new DateTime($publishedAt);
                $now = new DateTime();
                $interval = $now->diff($dateTime);

                if ($interval->y > 0) {
                    $formattedDate = $interval->y . ' year' . ($interval->y > 1 ? 's' : '') . ' ago';
                } elseif ($interval->m > 0) {
                    $formattedDate = $interval->m . ' month' . ($interval->m > 1 ? 's' : '') . ' ago';
                } elseif ($interval->d > 0) {
                    $formattedDate = $interval->d . ' day' . ($interval->d > 1 ? 's' : '') . ' ago';
                } elseif ($interval->h > 0) {
                    $formattedDate = $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' ago';
                } elseif ($interval->i > 0) {
                    $formattedDate = $interval->i . ' minute' . ($interval->i > 1 ? 's' : '') . ' ago';
                } else {
                    $formattedDate = $interval->s . ' second' . ($interval->s > 1 ? 's' : '') . ' ago';
                }

                $videoUrl = "https://www.youtube.com/watch?v={$videoId}";

                echo '<a class="frm_yt_post" href="' . $videoUrl . '" target="_blank" title="' . $title . '">
                    <div class="frm_yt_post-thumb">
                        <img src="' . $thumbnailUrl . '" alt="' . $title . '" title="' . $title . '" width="640" height="480" />
                    </div>
                    <div class="frm_yt_post-content">
                        <div class="frm_yt_post-title">
                            <h3>"' . $title . '"</h3>
                        </div>
                        <p class="frm_yt_post-date"><strong>Date:</strong> ' . $formattedDate . '</p>
                        <p class="frm_yt_post-excerpt">' . $description . '</p>
                    </div>
                </a>';
            }

            if($action == 1){
                echo '</div>';
            }

            if (!empty($nextPageToken)){
                echo '<div class="frm_yt_feed-load">
                    <button type="button" id="load_more_button" data-next_page_token="' . $nextPageToken . '" title="Load More">Load More</button>
                </div>';
            }

            if($action == 1){
                echo '</div>';
            }

        } else {
            echo "No se encontraron videos.";
        }
    }
    
}
