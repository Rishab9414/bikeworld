<?php

return [
    'recommended_width' => (int) env('BANNER_WIDTH', 1920),
    'recommended_height' => (int) env('BANNER_HEIGHT', 860),
    'min_width' => 1600,
    'min_height' => 520,
    'max_file_size_kb' => 5120,
    'aspect_ratio' => '16:7',
];
