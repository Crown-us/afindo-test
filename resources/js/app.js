import Alpine from 'alpinejs';
import lessonSorter from './lesson-sorter';
import videoUploader from './video-uploader';

Alpine.data('lessonSorter', lessonSorter);
Alpine.data('videoUploader', videoUploader);

window.Alpine = Alpine;

Alpine.start();
