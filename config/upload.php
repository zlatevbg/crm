<?php

return [

    'imagesDirectory' => 'images',
    'chunksDirectory' => 'chunks',
    'originalDirectory' => 'o',
    'cropDirectory' => 'c',
    'resizeDirectory' => 'r',
    'thumbnailDirectory' => 't',
    'thumbnailSmallDirectory' => 's',
    'thumbnailExtraSmallDirectory' => 'xs',
    'thumbnailMediumDirectory' => 'm',
    'thumbnailLargeDirectory' => 'l',
    'thumbnailExtraLargeDirectory' => 'xl',

    'imageExtensions' => ['jpg', 'png', 'gif', 'jpeg'],
    'fileExtensions' => ['jpg', 'png', 'gif', 'jpeg', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip', 'rar', 'ppt', 'pptx', 'ai', 'psd', 'mp4', 'dwg', 'eps'],
    'quality' => 90,

    'resizeWidth' => 600,
    'resizeHeight' => 600,

    'resizeMaxWidth' => 1920,
    'resizeMaxHeight' => 1000,

    'imageMaxWidth' => 1920,
    'imageMaxHeight' => 1920,

    'newsCropWidth' => 768,
    'newsCropHeight' => 432,

    'thumbnailWidth' => 120,
    'thumbnailHeight' => 68,
    'thumbnailSmallWidth' => 640,
    'thumbnailSmallHeight' => 360,
    'thumbnailExtraSmallWidth' => 320,
    'thumbnailExtraSmallHeight' => 180,
    'thumbnailMediumWidth' => 800,
    'thumbnailMediumHeight' => 450,
    'thumbnailLargeWidth' => 1024,
    'thumbnailLargeHeight' => 576,
    'thumbnailExtraLargeWidth' => 1280,
    'thumbnailExtraLargeHeight' => 720,

    'canvasBackground' => [255, 255, 255, 0], // transparent [png] or white [jpg]

    'watermarkImage' => storage_path('app/watermark.png'),
    'watermarkPosition' => 'bottom-right',
    'watermarkOffsetX' => 20,
    'watermarkOffsetY' => 20,

];
