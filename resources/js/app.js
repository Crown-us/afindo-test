import Alpine from 'alpinejs';
import lessonSorter from './lesson-sorter';

Alpine.data('lessonSorter', lessonSorter);

window.Alpine = Alpine;

Alpine.start();
