import './bootstrap';
import { RenderingEngine, Enums, init } from '@cornerstonejs/core';
import createImageIdsAndCacheMetaData from './createImageIdsAndCacheMetaData';

// import cornerstone from 'cornerstone-core';
// import cornerstoneWebImageLoader from 'cornerstone-web-image-loader';
// import cornerstoneTools from 'cornerstone-tools';

window.RenderingEngine = RenderingEngine;
window.Enums = Enums;
window.createImageIdsAndCacheMetaData = createImageIdsAndCacheMetaData;

// window.cornerstoneWebImageLoader = cornerstoneWebImageLoader;
// window.cornerstone = cornerstone;
// window.cornerstoneTools = cornerstoneTools;

// // Initialize CornerstoneJS
init();
