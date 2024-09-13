<?php
if (isset($_GET['m3u8_url'])) {
    $m3u8_url = urldecode($_GET['m3u8_url']);

    // Generate a unique file name for the converted file
    $output_file = tempnam(sys_get_temp_dir(), 'video_') . ".mp4";
    
    // Use ffmpeg to convert m3u8 to mp4
    $command = "C:/ffmpeg-7.0.1-full_build/bin/ffmpeg -i " . escapeshellarg($m3u8_url) . " -c copy " . escapeshellarg($output_file) . " 2>&1";
    $output = shell_exec($command);
    error_log("ffmpeg command output: " . $output);

    if (file_exists($output_file)) {
        header('Content-Type: video/mp4');
        header('Content-Disposition: attachment; filename="video.mp4"');
        readfile($output_file);
        unlink($output_file); // Delete the temporary file after download
    } else {
        echo "Failed to convert m3u8 to MP4. Check the error log for details.";
    }
} else {
    echo "No m3u8 URL provided.";
}
?>
