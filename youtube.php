<?php
ini_set('max_execution_time', 300); // Increase execution time to 5 minutes

$error = "";
$download_links = array();

if (isset($_POST['video_link'])) {
    $video_link = escapeshellarg($_POST['video_link']);
    $download_links = getDownloadLinks($video_link);
}

function getDownloadLinks($url) {
    global $error; // Access the global error variable
    $download_links = array();
    $command = "c:/yt-dlp --get-url $url 2>&1"; // Adjust the path to yt-dlp if necessary
    $output = shell_exec($command);

    if ($output === null) {
        $error = "Failed to execute yt-dlp command. Check your configuration.";
        return $download_links;
    }

    if (strpos($output, 'http') !== false) {
        $urls = explode("\n", trim($output));
        foreach ($urls as $link) {
            if (filter_var($link, FILTER_VALIDATE_URL)) {
                $download_links[] = array('quality' => 'Best Available', 'url' => $link);
            }
        }
    } else {
        $error = "Could not fetch download links. Please check the URL.";
    }
    return $download_links;
}

function convertVideoToMP4($inputFile, $outputFile) {
    $command = "C:/ffmpeg-7.0.1-full_build/bin/ffmpeg -i \"$inputFile\" -c:v libx264 -c:a aac -strict experimental \"$outputFile\" 2>&1";
    $output = shell_exec($command);
    if ($output === null) {
        global $error;
        $error = "Failed to execute ffmpeg command. Check your configuration.";
        return false;
    }
    return true;
}

if (isset($_POST['submit']) && !empty($download_links)) {
    $tempFile = tempnam(sys_get_temp_dir(), 'video_') . '.tmp';
    $mp4File = tempnam(sys_get_temp_dir(), 'video_') . '.mp4';
    
    foreach ($download_links as $link) {
        // Download the video file
        $downloadCommand = "curl -L -o \"$tempFile\" \"" . $link['url'] . "\" 2>&1";
        $downloadOutput = shell_exec($downloadCommand);

        if (file_exists($tempFile) && filesize($tempFile) > 0) {
            // Convert to MP4 format
            if (convertVideoToMP4($tempFile, $mp4File)) {
                $download_links[] = array('quality' => 'Converted MP4', 'url' => $mp4File);
            } else {
                $error = "Failed to convert video to MP4 format.";
            }
        } else {
            $error = "Failed to download video. Details: " . htmlspecialchars($downloadOutput);
        }

        // Cleanup
        if (file_exists($tempFile)) unlink($tempFile);
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Download Video</title>
    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400&display=swap" rel="stylesheet">
    <!-- Bootstrap core CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
        }
        .formSmall {
            width: 700px;
            margin: 20px auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <form method="post" action="" class="formSmall">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h2>Download Video</h2>
                </div>
                <div class="col-lg-12 text-center">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-secondary"><i class="fab fa-youtube"></i></button>
                        <button type="button" class="btn btn-outline-secondary"><i class="fab fa-facebook"></i></button>
                        <button type="button" class="btn btn-outline-secondary"><i class="fab fa-instagram"></i></button>
                        <button type="button" class="btn btn-outline-secondary"><i class="fab fa-pinterest"></i></button>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="input-group mt-3">
                        <input type="text" class="form-control" name="video_link" placeholder="Paste link.. e.g. https://www.youtube.com/watch?v=5cpIZ8zHHXw" value="<?php if(isset($_POST['video_link'])) echo htmlspecialchars($_POST['video_link']); ?>">
                        <div class="input-group-append">
                            <button type="submit" name="submit" id="submit" class="btn btn-primary">Go!</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <?php if (!empty($download_links)) { ?>
            <div class="row mt-3">
                <div class="col-lg-12">
                    <h5>Download Links</h5>
                </div>
                <div class="col-lg-12">
                    <ul>
                        <?php foreach ($download_links as $link) { ?>
                            <li>
                                <a href="<?php echo htmlspecialchars($link['url']); ?>" download>Download Here</a>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        <?php } elseif ($error) { ?>
            <div class="row mt-3">
                <div class="col-lg-12">
                    <h5>Error</h5>
                </div>
                <div class="col-lg-12">
                    <p><?php echo htmlspecialchars($error); ?></p>
                </div>
            </div>
        <?php } ?>
    </div>
</body>
</html>
