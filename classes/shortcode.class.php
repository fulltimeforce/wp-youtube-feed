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

        // Verifica que los datos del feed no estén vacíos
        if (!empty($feed_data)) {
            $feed_data = $feed_data[0];
            $channel_id = $feed_data['channel_id'];
            $google_key = $feed_data['google_key'];

            $url = "https://www.googleapis.com/youtube/v3/playlistItems?part=snippet,contentDetails&maxResults=50&playlistId=$channel_id&key=$google_key";

            $response = file_get_contents($url);

            // Verificar si la solicitud fue exitosa
            if ($response !== FALSE) {
                // Convertir la respuesta en formato JSON
                $data = json_decode($response, true);
                $this->get_posts($data);
            } else {
                echo 'Error al realizar la solicitud';
            }
        } else {
            echo 'No se encontraron datos para el feed ID proporcionado';
        }
    }

    public function get_posts($data)
    {
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $id = $item['id'];
                $title = $item['snippet']['title'];
                $description = $item['snippet']['description'];
                $publishedAt = $item['snippet']['publishedAt'];
                $thumbnailUrl = $item['snippet']['thumbnails']['default']['url'];

                // Convertir la cadena de fecha y hora a un objeto DateTime
                $dateTime = new DateTime($publishedAt);
                // Formatear la fecha y la hora
                $formattedDate = $dateTime->format('d M Y'); // Ejemplo: 21 Nov 2023
                $formattedTime = $dateTime->format('H:i:s'); // Ejemplo: 07:00:12

                echo "<div class='frm_yt_post'>";
                echo "<h3>{$title}</h3>";
                echo "<p><strong>Date:</strong> {$formattedDate}</p>";
                echo "<p><strong>Time:</strong> {$formattedTime}</p>";
                echo "<p>{$description}</p>";
                echo "<img src='{$thumbnailUrl}' alt='{$title}' title='{$title}' />";
                echo "</div>";
            }
        } else {
            echo "No posts found.";
        }
    }
}
